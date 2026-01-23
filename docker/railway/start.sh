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

# Show the actual Nginx config being used
echo "========================================="
echo "Nginx configuration after PORT substitution:"
cat /etc/nginx/nginx.conf
echo "========================================="

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
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &
SUPERVISOR_PID=$!

# Wait for services to start
echo "Waiting for services to fully initialize..."
sleep 3

# Wait until Nginx is actually accepting connections
echo "Waiting for Nginx to be ready to accept connections..."
for i in {1..30}; do
    if curl -f -s http://localhost:$PORT/ > /dev/null 2>&1; then
        echo "✓ Nginx is ready and accepting connections!"
        break
    fi
    echo "  Attempt $i/30: Nginx not ready yet, waiting..."
    sleep 1
done

# Verify services are listening
echo "========================================="
echo "Verifying services are listening..."
echo "========================================="
netstat -tuln 2>/dev/null || ss -tuln
echo "========================================="

# Test if Nginx is actually responding
echo "Testing Nginx HTTP response..."
curl -v http://localhost:$PORT/ 2>&1 | head -n 20 || echo "Curl failed"
echo "========================================="

# Show Nginx error log if there are any errors
echo "Checking Nginx error log..."
cat /var/log/nginx/error.log 2>/dev/null || echo "No error log yet"
echo "========================================="

# Keep supervisor in foreground and show continuous logs
echo "Container is ready and monitoring logs..."
tail -f /var/log/nginx/access.log /var/log/nginx/error.log 2>/dev/null &
wait $SUPERVISOR_PID