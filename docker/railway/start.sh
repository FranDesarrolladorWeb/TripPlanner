#!/bin/bash

# Set default PORT if not provided
export PORT=${PORT:-8080}

echo "========================================="
echo "Starting TripPlanner on port $PORT"
echo "========================================="

# Substitute PORT in nginx config
echo "Configuring Nginx with PORT=$PORT"
envsubst '${PORT}' < /etc/nginx/nginx.conf > /etc/nginx/nginx.conf.tmp
mv /etc/nginx/nginx.conf.tmp /etc/nginx/nginx.conf

# Set proper permissions first
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/var

# Run database migrations (don't fail if this doesn't work)
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod || echo "Warning: Migrations failed, continuing anyway..."

# Test nginx config
echo "Testing Nginx configuration..."
nginx -t

# Show final configuration
echo "========================================="
echo "Configuration complete!"
echo "PORT: $PORT"
echo "APP_ENV: $APP_ENV"
echo "PHP_VERSION: $(php -v | head -n 1)"
echo "Starting PHP-FPM and Nginx via supervisor..."
echo "========================================="

# Test PHP-FPM configuration
echo "Testing PHP-FPM configuration..."
php-fpm -t

# Start supervisor (which starts PHP-FPM and Nginx)
echo "Launching supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf