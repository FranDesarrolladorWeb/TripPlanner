#!/bin/bash
set -e

# Set default PORT if not provided
export PORT=${PORT:-8080}

echo "Starting TripPlanner on port $PORT"

# Substitute PORT in nginx config
envsubst '${PORT}' < /etc/nginx/nginx.conf > /etc/nginx/nginx.conf.tmp
mv /etc/nginx/nginx.conf.tmp /etc/nginx/nginx.conf

# Clear Symfony cache for production
php bin/console cache:clear --env=prod --no-debug

# Run database migrations
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Warm up cache
php bin/console cache:warmup --env=prod

# Set proper permissions
chown -R www-data:www-data /var/www/html/var

# Start supervisor (which starts PHP-FPM and Nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf