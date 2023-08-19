# Для начала указываем исходный образ, он будет использован как основа
FROM php:8.2.5-fpm

RUN touch /var/log/fpm-php.www.log && chmod 777 /var/log/fpm-php.www.log

# RUN выполняет идущую за ней команду в контексте нашего образа.
# В данном случае мы установим некоторые зависимости и модули PHP.
# Для установки модулей используем команду docker-php-ext-install.
# На каждый RUN создается новый слой в образе, поэтому рекомендуется объединять команды.
RUN apt-get update && apt-get install -y \
        curl \
        wget \
        git \
        libmcrypt-dev \
        libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libwebp-dev \
    && docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) iconv mbstring mysqli pdo_mysql zip sockets \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-install pcntl

RUN docker-php-ext-install calendar && docker-php-ext-enable calendar

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

# Куда же без composer'а.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Добавим свой php.ini, можем в нем определять свои значения конфига
ADD ./php.ini /usr/local/etc/php/conf.d/40-custom.ini

WORKDIR /app

CMD ["php-fpm"]
