FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install  -y \
    git \
    curl \
    libcurl4-openssl-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev

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

# Create database directory and set permissions
RUN mkdir -p /var/www/database && chown -R www-data:www-data /var/www/database

# Run database setup and seeding (continue on errors to see all issues)
RUN chown www-data:www-data /var/www/database/belt.db || echo "Chown failed" && \
    php database/setup.php || echo "Database setup failed"

# Expose port 9000
EXPOSE 9000
CMD ["php-fpm"]