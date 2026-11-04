# Run the PHP app with SQLite for local use and PostgreSQL for Supabase/Vercel.
FROM php:8.1-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev libpq-dev zip unzip curl \
    && docker-php-ext-install pdo_sqlite pdo_pgsql \
    && a2enmod rewrite \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html/

RUN composer install --no-dev --optimize-autoloader --no-progress --no-suggest \
    && mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
