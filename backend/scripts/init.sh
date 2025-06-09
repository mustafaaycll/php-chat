#!/bin/sh
set -e

DB_PATH="/app/data/php-chat.db"
SCHEMA_PATH="/app/src/Schema/schema.sql"

if [ ! -f "$DB_PATH" ]; then
    echo "==> Creating database..."
    touch "$DB_PATH"
    chmod 777 "$DB_PATH"
    echo "==> Applying schema..."
    sqlite3 "$DB_PATH" < "$SCHEMA_PATH"
    echo "==> Database ready."
else
    echo "==> Database already exists, skipping."
fi

exec php-fpm -F