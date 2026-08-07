#!/bin/sh
set -e

echo "Esperando a PostgreSQL..."
until php -r "new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" 2>/dev/null; do
  sleep 2
done

echo "PostgreSQL listo. Ejecutando migraciones y seeders..."
php artisan migrate --force --seed

echo "Iniciando servidor API en :8000"
php artisan serve --host=0.0.0.0 --port=8000
