#!/bin/bash
set -e  # exit on first error

# Wait for database
echo "Waiting for DB..."
until php check-db.php; do
  sleep 2
done

# Clear config cache
php artisan config:clear

# Run migrations
php artisan migrate --force

# Start Laravel dev server
php artisan serve --host=0.0.0.0 --port=${PORT}
