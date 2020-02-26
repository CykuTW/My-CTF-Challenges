FROM php:7-apache

RUN apt-get -y update
RUN apt-get -y install xvfb libfontconfig wkhtmltopdf
RUN a2enmod rewrite
RUN chown root:root /var/www/html

WORKDIR /var/www/html/

COPY ./www/*.php ./
COPY ./www/.htaccess ./
COPY ./www/templates/ ./templates

RUN mkdir -p ./screenshots
RUN chown www-data:www-data ./screenshots

COPY flag /here_is_your_flag
COPY readflag.c /readflag.c
RUN gcc /readflag.c -o /readflag
RUN chmod 755 /readflag
RUN chmod u+s /readflag

RUN chmod 400 /here_is_your_flag
