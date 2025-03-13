FROM php:7.4-fpm

# Встановлення необхідних пакетів для компіляції Xdebug
RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    pkg-config \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# Встановлення Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Завантаження Xdebug та його компіляція
RUN curl -L https://xdebug.org/files/xdebug-2.9.8.tgz -o /tmp/xdebug.tgz \
    && tar -xzvf /tmp/xdebug.tgz -C /tmp \
    && cd /tmp/xdebug-2.9.8 \
    && phpize \
    && ./configure \
    && make \
    && make install \
    && rm -rf /tmp/xdebug*

# Переконаємося, що директорія існує
RUN mkdir -p /etc/php/7.4/fpm/conf.d/

# Активуємо Xdebug в PHP
RUN echo "zend_extension=$(find / -name 'xdebug.so')" > /etc/php/7.4/fpm/conf.d/20-xdebug.ini

# Встановлення Xdebug налаштувань
COPY ./xdebug.ini /etc/php/7.4/fpm/conf.d/20-xdebug.ini
