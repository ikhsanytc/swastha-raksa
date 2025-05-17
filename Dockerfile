FROM php:8.2-apache

# Install ekstensi PHP yang diperlukan
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

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Copy semua file ke folder kerja container
COPY . /var/www/html/

RUN composer install --no-dev

# Ubah document root Apache ke /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Sesuaikan konfigurasi Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Ubah permission supaya Apache bisa akses file
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
