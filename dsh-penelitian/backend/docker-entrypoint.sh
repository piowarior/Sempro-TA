#!/bin/sh
set -e

if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

# Wait for MySQL to be ready
echo "Waiting for MySQL database connection..."
until php -r "try { new PDO('mysql:host=' . (getenv('DB_HOST') ?: 'mysql') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . (getenv('DB_DATABASE') ?: 'dsh_db'), (getenv('DB_USERNAME') ?: 'dsh_user'), (getenv('DB_PASSWORD') ?: 'dsh_password')); exit(0); } catch (Exception \$e) { exit(1); }"; do
    echo "MySQL not ready yet. Waiting 2 seconds..."
    sleep 2
done
echo "MySQL is reachable!"

# Run migrations and seeders if needed
if [ "$1" = "php" ] && [ "$2" = "artisan" ] && [ "$3" = "serve" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
    echo "Running database seeders..."
    php artisan db:seed --force
fi

exec "$@"

