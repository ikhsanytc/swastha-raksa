# Gunakan image PHP bawaan dengan Apache
FROM php:8.2-apache

# Install extensions PHP yang dibutuhkan CodeIgniter 4
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    zip \
    && docker-php-ext-install zip pdo pdo_mysql

# Aktifkan mod_rewrite untuk Apache (CI4 butuh untuk routing)
RUN a2enmod rewrite

# Copy project ke dalam container
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Ubah permission writable folder
RUN chown -R www-data:www-data /var/www/html/writable \
    && chmod -R 775 /var/www/html/writable

# Expose port 80
EXPOSE 80
