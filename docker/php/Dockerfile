FROM php:8.4-fpm

# php.ini のコピー
COPY php.ini /usr/local/etc/php/

# 必要なパッケージのインストール
RUN apt-get update && apt-get install -y \
    default-mysql-client zlib1g-dev libzip-dev unzip curl git \
    && docker-php-ext-install pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


# Composer のインストール
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# 作業ディレクトリの設定
WORKDIR /var/www
