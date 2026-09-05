<?php
/**
 * VolNet Universal Database Configuration
 * Supports both MySQL/MariaDB (MySQLi) and Supabase/PostgreSQL (PDO)
 */

// 1. Load .env configuration
$env_file = dirname(__DIR__) . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim(trim($value), "\"'");
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

// Helper to get environment variables across getenv, $_ENV, and $_SERVER
function get_env_var($key, $default = null) {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

// 2. Read database settings
$db_driver = strtolower(get_env_var('DB_DRIVER', 'mysql'));
$db_host   = get_env_var('DB_HOST', 'localhost');
$db_user   = get_env_var('DB_USER', 'root');
$db_pass   = get_env_var('DB_PASS', '');
$db_name   = get_env_var('DB_NAME', 'volnet1');
$db_port   = (int)get_env_var('DB_PORT', ($db_driver === 'pgsql' ? 5432 : 3306));
$db_socket = get_env_var('DB_SOCKET', (file_exists('/tmp/mariadb_volnet.sock') ? '/tmp/mariadb_volnet.sock' : ini_get('mysqli.default_socket')));

// Global connection variables for legacy compatibility
$host     = $db_host;
$user     = $db_user;
$username = $db_user;
$password = $db_pass;
$database = $db_name;
$dbname   = $db_name;

if (!defined('MYSQLI_ASSOC')) define('MYSQLI_ASSOC', 1);
if (!defined('MYSQLI_NUM')) define('MYSQLI_NUM', 2);
if (!defined('MYSQLI_BOTH')) define('MYSQLI_BOTH', 3);

// 3. PostgreSQL / Supabase Driver (via PDO with MySQLi Compatibility Wrapper)
if ($db_driver === 'pgsql' || $db_port === 5432 || $db_port === 6543) {

    class VolNetPgResult {
        private $stmt;
        private $rows = null;
        private $cursor = 0;
        public $num_rows = 0;

        public function __construct($stmt) {
            $this->stmt = $stmt;
            if ($stmt instanceof PDOStatement) {
                $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $this->num_rows = count($this->rows);
            }
        }

        public function fetch_assoc() {
            if ($this->rows !== null && $this->cursor < $this->num_rows) {
                return $this->rows[$this->cursor++];
            }
            return null;
        }

        public function fetch_all($mode = MYSQLI_NUM) {
            if ($this->rows === null) return [];
            if ($mode === MYSQLI_ASSOC) {
                return $this->rows;
            }
            $result = [];
            foreach ($this->rows as $row) {
                $result[] = array_values($row);
            }
            return $result;
        }
    }

    class VolNetPgStmt {
        private $pdo;
        private $sql;
        private $stmt = null;
        private $params = [];
        public $insert_id = 0;
        public $error = '';

        public function __construct($pdo, $sql) {
            $this->pdo = $pdo;
            $this->sql = $sql;
        }

        public function bind_param($types, ...$args) {
            $this->params = $args;
            return true;
        }

        public function execute() {
            try {
                // If it's an INSERT and doesn't have RETURNING, append RETURNING id / primary key if needed
                $sql = $this->sql;
                $is_insert = stripos(trim($sql), 'INSERT INTO') === 0;

                $this->stmt = $this->pdo->prepare($sql);
                $result = $this->stmt->execute($this->params);

                if ($is_insert) {
                    try {
                        $this->insert_id = (int)$this->pdo->lastInsertId();
                    } catch (Exception $e) {
                        $this->insert_id = 0;
                    }
                }
                return $result;
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function get_result() {
            return new VolNetPgResult($this->stmt);
        }

        public function close() {
            $this->stmt = null;
            return true;
        }
    }

    class VolNetPgConnection {
        private $pdo;
        public $connect_error = null;
        public $error = '';
        public $insert_id = 0;

        public function __construct($host, $port, $dbname, $user, $pass) {
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
            try {
                $this->pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                $this->connect_error = $e->getMessage();
            }
        }

        public function prepare($sql) {
            // Replace NOW() with CURRENT_TIMESTAMP
            $sql = preg_replace('/\bNOW\(\)/i', 'CURRENT_TIMESTAMP', $sql);
            return new VolNetPgStmt($this->pdo, $sql);
        }

        public function query($sql) {
            try {
                $sql = preg_replace('/\bNOW\(\)/i', 'CURRENT_TIMESTAMP', $sql);
                $stmt = $this->pdo->query($sql);
                return new VolNetPgResult($stmt);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        public function set_charset($charset) {
            return true;
        }

        public function real_escape_string($str) {
            if ($str === null) return '';
            return str_replace("'", "''", (string)$str);
        }

        public function escape_string($str) {
            return $this->real_escape_string($str);
        }

        public function close() {
            $this->pdo = null;
            return true;
        }
    }

    $conn = new VolNetPgConnection($db_host, $db_port, $db_name, $db_user, $db_pass);
    if ($conn->connect_error) {
        die("Supabase / PostgreSQL connection failed: " . $conn->connect_error);
    }

} else {
    // 4. Standard MySQL / MariaDB Driver (MySQLi)
    if (!empty($db_socket) && file_exists($db_socket)) {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port, $db_socket);
    } else {
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    }

    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
}
