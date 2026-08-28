FROM php:8.2-apache

RUN docker-php-ext-install mysqli

RUN apt-get update \
    && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

RUN echo "upload_max_filesize = 25M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 30M" >> /usr/local/etc/php/conf.d/uploads.ini

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads \
    && chmod 755 /var/www/html/uploads

EXPOSE 80