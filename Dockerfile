FROM php:8.2-fpm

RUN apt-get update && apt-get install -y git libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html
COPY . .

EXPOSE 80

CMD ["php-fpm", "-F"]

