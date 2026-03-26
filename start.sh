#!/bin/sh

# ---------------------------
# Create .env from .env.example if missing
# ---------------------------
if [ ! -f /var/www/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/.env.example /var/www/.env
fi

# ---------------------------
# Generate APP_KEY if empty
# ---------------------------
if ! grep -q '^APP_KEY=base64:' /var/www/.env; then
    echo "APP_KEY is missing. Generating..."
    php /var/www/artisan key:generate --ansi --force
fi

# -------------------------------
# Install Composer dependencies if missing
# -------------------------------
if [ ! -d /var/www/vendor ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --optimize-autoloader --ignore-platform-reqs
fi

# ---------------------------
# Wait for MySQL to be ready
# ---------------------------
echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
until mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" --ssl=0 -e "SELECT 1" >/dev/null 2>&1; do
    echo "MySQL not ready yet. Sleeping 2s..."
    sleep 2
done
echo "MySQL is ready!"

# ---------------------------
# Run migrations
# ---------------------------
echo "Running migrations..."
php /var/www/artisan migrate --force

# ---------------------------
# Seed database if users table is empty
# ---------------------------
USERS_COUNT=$(php /var/www/artisan tinker --execute="echo \App\Models\User::count();")
if [ "$USERS_COUNT" -eq 0 ]; then
    echo "Seeding database..."
    php /var/www/artisan db:seed --force
fi

# ---------------------------
# Clear & warm up caches
# ---------------------------
echo "Clearing caches..."
php /var/www/artisan config:clear
php /var/www/artisan view:clear
php /var/www/artisan route:clear

echo "Caching config & routes for performance..."
php /var/www/artisan config:cache
php /var/www/artisan route:cache
php /var/www/artisan view:cache

# ---------------------------
# Print info at the very end
# ---------------------------
echo "======================================="
echo " Laravel Payroll System is ready! "
echo " URL: http://localhost:8000"
echo " MySQL: host=$DB_HOST, port=$DB_PORT"
echo " Username: $DB_USERNAME"
echo " Password: $DB_PASSWORD"
echo " Database: $DB_DATABASE"
echo " Admin account seeded: admin@dole9.gov.ph / Admin@DOLE9!"
echo " Adminer (DB GUI): http://localhost:8080"
echo "======================================="

# # ---------------------------
# # Start Laravel dev server
# # ---------------------------
# php /var/www/artisan serve --host=0.0.0.0 --port=8000

# ---------------------------
# Start queue worker (background jobs)
# ---------------------------
echo "Starting queue worker..."
php /var/www/artisan queue:work --daemon --sleep=3 --tries=3 &

# ---------------------------
# Start scheduler (cache refresh polling)
# ---------------------------
echo "Starting scheduler..."
php /var/www/artisan schedule:work &

# ---------------------------
# Start PHP-FPM
# ---------------------------
echo "Starting PHP-FPM..."
php-fpm -D

# ---------------------------
# Start Nginx (foreground — keeps container alive)
# ---------------------------
echo "Starting Nginx..."
nginx -g "daemon off;"