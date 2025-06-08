FROM php:8.4-fpm

# php.ini のコピー
COPY php.ini /usr/local/etc/php/

# 必要なパッケージのインストール

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip unzip \
    libzip-dev \
    pkg-config \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo_mysql


# Composer のインストール
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# 作業ディレクトリの設定
WORKDIR /var/www
