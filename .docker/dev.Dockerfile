FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libjpeg-dev \
    libpng-dev \
    libzip-dev \
    git \
    zip \
    libxml2-dev \
    default-mysql-client \
    unzip \
    apt-utils \
    mailutils

# configure driver
RUN docker-php-ext-configure gd --with-jpeg

# install driver
RUN docker-php-ext-install pdo pdo_mysql dom zip gd intl fileinfo iconv

# install xdebug
# client_host is your linux system ip (hostname -I) or host.docker.internal with docker compose extra host
RUN pecl install xdebug-3.1.3 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# install nodeJs
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt install -y nodejs

# install symfony cli
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt-get install -y symfony-cli

WORKDIR /usr/src/myapp
