# Gunakan image PHP 8.1 resmi dengan server Apache
FROM php:8.1-apache

# Install ekstensi PHP yang diperlukan untuk koneksi ke MySQL/MariaDB
RUN docker-php-ext-install pdo pdo_mysql

# Salin semua kode aplikasi (index.php, dll) dari repo 
# ke dalam direktori web server di dalam container
COPY . /var/www/html/

# Atur izin file agar Apache bisa membacanya
RUN chown -R www-data:www-data /var/www/html