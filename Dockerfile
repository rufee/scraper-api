FROM php:8.3-fpm
RUN usermod -u 1000 www-data
RUN groupmod -g 1000 www-data

RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev zip unzip git

RUN pecl install redis
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd pdo opcache && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install

EXPOSE 9000
CMD ["php-fpm"]
