# Simple Dockerfile for running Mitas PHP + SQLite app
FROM php:8.1-apache

# Install SQLite, Composer dependencies, and common utils
RUN apt-get update \
    && apt-get install -y libsqlite3-dev zip unzip curl \
    && docker-php-ext-install pdo_sqlite \
    && a2enmod rewrite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy app
COPY . /var/www/html/

# Install PHP dependencies when building image
RUN composer install --no-dev --optimize-autoloader --no-progress --no-suggest

# Ensure data directory exists and permissions
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
