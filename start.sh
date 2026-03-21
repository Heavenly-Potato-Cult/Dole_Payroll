#!/bin/sh

# # Wait for MySQL to be ready
# echo "Waiting for MySQL at $DB_HOST:$DB_PORT..."
# while ! mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; do
#     echo "MySQL not ready yet. Sleeping 2s..."
#     sleep 2
# done
# echo "MySQL is ready!"

# Run migrations and seed only once
if [ ! -f /var/www/.laravel_initialized ]; then
    echo "Running Laravel setup..."
    php artisan key:generate --force
    php artisan migrate --force
    php artisan db:seed --force
    touch /var/www/.laravel_initialized
fi

# Print info at the very end, only once
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

# Start Laravel dev server
php artisan serve --host=0.0.0.0 --port=8000