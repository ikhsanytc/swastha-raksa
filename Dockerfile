FROM php:8.2-apache

# Install dependencies termasuk ekstensi intl
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    && docker-php-ext-install \
    intl \
    mysqli \
    pdo \
    pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Salin semua file project ke container
COPY . .

# Jalankan composer install
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set DocumentRoot ke folder public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update konfigurasi Apache untuk pakai folder public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Ganti owner agar bisa dibaca Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
