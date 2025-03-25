FROM php:7.4-fpm

RUN apt-get update && \
    apt-get install -y \
    git \
    zip \
    curl \
    ca-certificates \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN docker-php-ext-install mysqli
# CA 인증서 업데이트 (중요)
RUN update-ca-certificates  
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# php.ini의 디렉토리 경로
# 기본적으로 PHP 설정 파일은 /usr/local/etc/php/php.ini 에 있습니다.
# custom-php.ini 파일을 복사하여 설정을 덮어씁니다.
COPY ./custom-php.ini /usr/local/etc/php/conf.d/

# 작업 디렉토리 설정
WORKDIR /var/www/html

EXPOSE 9000

ENTRYPOINT ["php-fpm"]