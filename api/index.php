<?php
/**
 * Vercel Serverless Entry Router
 * Dispatches all dynamic incoming requests to the appropriate PHP file
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

// Root path
if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    require __DIR__ . '/../index.php';
    exit;
}

$file = dirname(__DIR__) . $uri;

// Exact file match
if (file_exists($file) && !is_dir($file)) {
    require $file;
    exit;
}

// Append .php if omitted
if (file_exists($file . '.php')) {
    require $file . '.php';
    exit;
}

// Check for directory index.php
if (is_dir($file) && file_exists($file . '/index.php')) {
    require $file . '/index.php';
    exit;
}

http_response_code(404);
echo "404 Not Found: " . htmlspecialchars($uri);
