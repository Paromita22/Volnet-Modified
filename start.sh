#!/usr/bin/env bash
# ==========================================================
# VolNet Start Script for Arch Linux
# ==========================================================

# Always work from the project root directory
cd "$(dirname "$0")" || exit 1

PORT=8000
DB_DIR="$HOME/.local/share/mariadb_volnet"
SOCKET="/tmp/mariadb_volnet.sock"

# Free port 8000 if already occupied by an old PHP server instance
if lsof -i :$PORT -t >/dev/null 2>&1; then
    echo "Port $PORT is currently in use. Freeing port $PORT..."
    kill -9 $(lsof -i :$PORT -t) 2>/dev/null || true
    sleep 1
elif fuser $PORT/tcp >/dev/null 2>&1; then
    fuser -k $PORT/tcp >/dev/null 2>&1 || true
    sleep 1
fi

# Detect active database driver from .env
DRIVER=$(grep '^DB_DRIVER=' .env 2>/dev/null | cut -d '=' -f2 | tr -d ' "' | tr '[:upper:]' '[:lower:]')

echo "=========================================================="
if [ "$DRIVER" = "pgsql" ]; then
    echo "  🚀 Database: Supabase (PostgreSQL)"
else
    echo "  🚀 Database: Local MariaDB / MySQL"
    # Initialize MariaDB datadir if not exists
    if [ ! -d "$DB_DIR" ]; then
        echo "Initializing MariaDB datadir at $DB_DIR..."
        mariadb-install-db --datadir="$DB_DIR"
    fi

    # Ensure MariaDB is running
    if ! mariadb-admin --socket="$SOCKET" ping >/dev/null 2>&1; then
        echo "Starting local MariaDB server..."
        mariadbd --datadir="$DB_DIR" --socket="$SOCKET" --port=3306 &
        sleep 2
    fi
fi

# Detect available PHP extensions
EXT_ARGS=("-d" "extension=mysqli" "-d" "extension=gd" "-d" "mysqli.default_socket=$SOCKET" "-d" "opcache.enable=0")

if php -d extension=pdo_pgsql -m 2>/dev/null | grep -q 'pdo_pgsql'; then
    EXT_ARGS+=("-d" "extension=pdo_pgsql")
fi
if php -d extension=pgsql -m 2>/dev/null | grep -q 'pgsql'; then
    EXT_ARGS+=("-d" "extension=pgsql")
fi

echo "=========================================================="
echo "  🌐 VolNet Server is LIVE at: http://localhost:$PORT"
echo "  ⚡ Press Ctrl+C in this terminal to stop the server"
echo "=========================================================="
echo ""

# Start the PHP development server
exec php "${EXT_ARGS[@]}" -S 0.0.0.0:$PORT
