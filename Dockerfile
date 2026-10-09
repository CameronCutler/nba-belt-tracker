FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libcurl4-openssl-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    nginx \
    supervisor \
    ca-certificates

# Clear cache \
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd curl

# Install Composer from its official installer and verify its published checksum
RUN EXPECTED_CHECKSUM="$(curl -fsSL https://composer.github.io/installer.sig)" \
    && curl -fsSL https://getcomposer.org/installer -o composer-setup.php \
    && ACTUAL_CHECKSUM="$(sha384sum composer-setup.php | cut -d ' ' -f 1)" \
    && [ "$EXPECTED_CHECKSUM" = "$ACTUAL_CHECKSUM" ] \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
RUN chown -R www-data:www-data /var/www

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader

RUN update-ca-certificates

# Replace nginx config entirely
RUN rm -rf /etc/nginx/conf.d/* /etc/nginx/sites-enabled/* /etc/nginx/sites-available/* /var/www/html
COPY nginx.conf /etc/nginx/nginx.conf

# Copy supervisord config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create storage directory for the SQLite DB (volume mount target)
RUN mkdir -p /var/www/storage && chown -R www-data:www-data /var/www/storage

# Entrypoint runs db setup then starts supervisord
COPY entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r//' /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]