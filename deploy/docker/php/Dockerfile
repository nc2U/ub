FROM php:7.4-fpm

RUN apt-get update && \
    apt-get install -y \
    git \
    zip \
    curl \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

RUN docker-php-ext-install mysqli
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 작업 디렉토리 설정
WORKDIR /var/www/html

# Composer를 통해 PHPMailer 설치
RUN composer require phpmailer/phpmailer

EXPOSE 9000

ENTRYPOINT ["php-fpm"]