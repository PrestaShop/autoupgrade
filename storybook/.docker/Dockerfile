FROM composer:2.4 AS build

COPY . /autoupgrade

WORKDIR /autoupgrade
RUN composer install --no-dev --no-interaction --optimize-autoloader

WORKDIR /autoupgrade/storybook
RUN composer install --no-dev --no-interaction --optimize-autoloader


FROM node:20
ENV PHP_URL http://storybook-php:8000

RUN apt update \
    && apt install -y php8.2 php-dom \
    && rm -rf /var/lib/apt/lists/*

COPY --from=build /autoupgrade /autoupgrade

WORKDIR /autoupgrade/_dev

RUN npm install --omit=dev

WORKDIR /autoupgrade/storybook

RUN npm install
