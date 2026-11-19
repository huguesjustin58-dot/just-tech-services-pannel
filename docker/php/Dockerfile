FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libpq-dev \
    libzip-dev \
    gmp-dev \
    oniguruma-dev \
    openssh-client \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        bcmath \
        gmp \
        intl \
        pcntl \
        sockets \
        zip \
        gd \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

RUN deluser www-data || true \
    && addgroup -g 1000 jts \
    && adduser -D -u 1000 -G jts jts \
    && mkdir -p /var/www/html \
    && chown -R jts:jts /var/www/html

WORKDIR /var/www/html
USER jts
