FROM php:8.2-fpm

ENV TZ Asia/Tokyo

# 必要パッケージのインストール
RUN apt-get update && \
    apt-get install -y \
        git \
        unzip \
        curl \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        gnupg \
        ca-certificates

# PHP拡張のインストール
RUN docker-php-ext-install intl pdo_mysql zip bcmath

# Node.js 18.x のインストール（公式推奨手順）
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g npm

# Composer のコピー
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# php.ini の設定
COPY ./docker/app/php.ini /usr/local/etc/php/php.ini

# 作業ディレクトリ
WORKDIR /app
