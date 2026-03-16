#!/bin/sh
set -e

echo "Running database setup..."
php /var/www/database/setup.php

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
