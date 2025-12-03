FROM php:8.1-apache

# Install dependencies sistem, zip, git (untuk composer)
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libzip-dev

# Install ekstensi PHP: pdo_mysql & xdebug (untuk coverage)
RUN docker-php-ext-install pdo pdo_mysql zip \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Konfigurasi Xdebug untuk Coverage
RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin kode aplikasi
COPY . /var/www/html/

# Atur izin
RUN chown -R www-data:www-data /var/www/html

# Install dependensi PHP (PHPUnit)
WORKDIR /var/www/html
RUN composer install --no-interaction --optimize-autoloader