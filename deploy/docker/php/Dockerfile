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
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install mysqli

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

EXPOSE 9000

ENTRYPOINT ["php-fpm"]