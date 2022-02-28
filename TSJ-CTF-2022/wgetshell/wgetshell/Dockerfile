FROM php:7.4-apache

RUN apt update && apt -y install wget

COPY wget.php /var/www/html/
RUN chmod 0444 /var/www/html/wget.php
RUN chown -R root:root /var/www && chmod 755 /var/www/html
RUN mkdir /tmp/sandbox && chmod 0113 /tmp/sandbox && chown root:root /tmp/sandbox
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY readflag.c /readflag.c
RUN chmod 0444 /readflag.c && gcc /readflag.c -o /readflag
RUN chown root:root /readflag && chmod 4555 /readflag

RUN echo 'TSJ{fake_flag}' > /flag
RUN chmod 0400 /flag && chown root:root /flag

RUN echo 'tries = 1' > /etc/wgetrc
RUN echo 'timeout = 5' >> /etc/wgetrc

EXPOSE 80
