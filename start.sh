#!/bin/sh

# Create .env from .env.example if missing
if [ ! -f /var/www/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/.env.example /var/www/.env
fi

# Generate APP_KEY if empty
APP_KEY=$(grep APP_KEY /var/www/.env | cut -d '=' -f2)
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php /var/www/artisan key:generate --ansi --force
fi

# Run migrations and seed only once
if [ ! -f /var/www/.laravel_initialized ]; then
    echo "Running Laravel setup..."
    php /var/www/artisan migrate --force
    php /var/www/artisan db:seed --force
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