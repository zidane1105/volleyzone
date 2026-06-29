#!/bin/sh

# Set correct permissions
chmod -R 777 /var/www/storage

# Run migrations
php artisan migrate --force

# Link storage
php artisan storage:link

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g "daemon off;"
