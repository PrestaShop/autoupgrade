FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libxml2-dev

RUN docker-php-ext-install dom xml

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

RUN node --version
RUN npm --version

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt-get install -y symfony-cli

WORKDIR /var/www/html
