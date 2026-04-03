#!/bin/sh

# php /app/artisan storage:link
php /app/artisan cache:clear
php /app/artisan config:clear
php /app/artisan optimize:clear

php /app/artisan migrate --force --path=database/migrations

chown -R www-data:www-data /app/storage

/usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
