#!/bin/sh
set -e

echo "Running migrations..."
php /var/www/database/migrate.php

echo "Seeding teams..."
php /var/www/database/seed_teams_simple.php

echo "Initialising belt..."
php /var/www/database/init_belt.php

echo "Fixing database permissions..."
chown -R www-data:www-data /var/www/database
find /var/www/database -type d -exec chmod 775 {} \;
find /var/www/database -type f -name "*.db" -exec chmod 664 {} \;

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
