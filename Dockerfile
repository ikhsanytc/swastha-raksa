FROM php:8.2-apache

# Install dependencies dan Composer
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    zip \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Install ekstensi PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Set working directory dan salin semua file
WORKDIR /var/www/html
COPY . .

# Jalankan composer install untuk install dependensi CI4
RUN composer install

# Ubah document root ke public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update konfigurasi apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Pastikan file dapat dibaca Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
