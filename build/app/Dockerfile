FROM php:8.2-apache

## Update package information
RUN apt-get update

COPY /apache/000-default.conf /etc/apache2/sites-available/000-default.conf

## Configure Apache
RUN a2enmod rewrite \
    && sed -i 's!/var/www/html!/var/www/public!g' /etc/apache2/sites-available/000-default.conf \
    && mv /var/www/html /var/www/public

## Install Composer
RUN curl -sS https://getcomposer.org/installer \
  | php -- --install-dir=/usr/local/bin --filename=composer

## Install Opcache
RUN docker-php-ext-install opcache


## Install zip libraries and extension
RUN apt-get install --yes git curl zlib1g-dev libzip-dev \
    && docker-php-ext-install zip

## Install intl library and extension
RUN apt-get install --yes libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# Install pdo_mysql
RUN docker-php-ext-install pdo_mysql \
    && docker-php-ext-enable pdo_mysql

# Install zip
RUN docker-php-ext-install zip \
    && apt-get install -y unzip

## Install bcmath
RUN docker-php-ext-install bcmath \
    && docker-php-ext-enable bcmath

## Install redis
RUN pecl install redis && docker-php-ext-enable redis


## Install supervisor
RUN apt-get update && apt-get install -y supervisor
COPY /supervisord/conf.d /etc/supervisor/conf.d/

# And clean up the image
RUN rm -rf /var/lib/apt/lists/*

EXPOSE 80

WORKDIR /var/www

CMD ["/usr/bin/supervisord"]