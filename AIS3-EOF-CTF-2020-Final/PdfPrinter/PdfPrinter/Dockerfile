FROM php:7.4.14-apache-buster

WORKDIR /var/www/
COPY src/ /var/www/pdf-printer/

ENV APACHE_DOCUMENT_ROOT /var/www/pdf-printer/public

RUN apt update && apt install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite

RUN cd pdf-printer; php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN cd pdf-printer; php composer-setup.php
RUN cd pdf-printer; php -r "unlink('composer-setup.php');"
RUN cd pdf-printer; php composer.phar install

RUN find /var/www/pdf-printer/storage/ -type d -exec chmod 733 {} \;
RUN find /var/www/pdf-printer/vendor/ -type d -exec chmod 733 {} \;

# reset APP_KEY to random string
RUN cd pdf-printer; php artisan key:generate

RUN echo "AIS3{fake_flag}" > /flag
RUN chmod 0400 /flag
COPY readflag.c /readflag.c
RUN gcc /readflag.c -o /readflag
RUN chmod 4755 /readflag
