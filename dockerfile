FROM php:8.1-apache

# Install ekstensi PHP untuk koneksi ke MySQL/MariaDB
RUN docker-php-ext-install pdo pdo_mysql

# Salin kode aplikasi
COPY index.php /var/www/html/

# Atur izin
RUN chown -R www-data:www-data /var/www/html