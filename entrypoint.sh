#!/bin/sh
set -e

echo "Running migrations..."
php /var/www/database/migrate.php

echo "Seeding teams..."
php /var/www/database/seed_teams_simple.php

echo "Initialising belt..."
php /var/www/database/init_belt.php

echo "Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
