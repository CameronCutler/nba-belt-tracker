#!/bin/sh
set -e

echo "Running database setup..."
php /var/www/database/setup.php

echo "Starting php-fpm..."
exec php-fpm
