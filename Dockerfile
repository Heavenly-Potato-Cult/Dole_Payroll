FROM php:8.2-cli

WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    zlib1g-dev \
    curl \
    zip \
    wget \
    sudo \
    default-mysql-client \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip xml curl bcmath opcache gd

 # Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Laravel project
COPY . .

# Permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Install Composer dependencies
RUN composer install --no-interaction --optimize-autoloader --ignore-platform-reqs

# Expose Laravel dev server
EXPOSE 8000
