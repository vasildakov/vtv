FROM php:8.2-cli

RUN apt-get update
RUN apt-get install --yes git

## Install Composer
RUN curl -sS https://getcomposer.org/installer \
  | php -- --install-dir=/usr/local/bin --filename=composer

EXPOSE 80

WORKDIR /var/www/html
VOLUME /var/www/html

CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]