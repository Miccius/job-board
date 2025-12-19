#!/bin/bash
set -e

echo "Waiting for DB..."
until php check-db.php; do
  sleep 2
done

php artisan config:clear
php artisan migrate --force

# FINE. NON AVVIARE SERVER
