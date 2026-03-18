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
    ca-certificates \
    cron

# Clear cache \
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd curl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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

# Create database directory and set permissions
RUN mkdir -p /var/www/database && chown -R www-data:www-data /var/www/database

# Cron job - runs seeder at midnight Pacific time
COPY crontab /etc/cron.d/nba-belt
RUN chmod 0644 /etc/cron.d/nba-belt && crontab /etc/cron.d/nba-belt

# Entrypoint runs db setup then starts supervisord
COPY entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r//' /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]