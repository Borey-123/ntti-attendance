FROM php:8.2-apache

# Install system dependencies and PHP extensions required for Laravel & DOMPDF
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    sqlite3 \
    libsqlite3-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html

# Install Composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create SQLite database file if it doesn't exist
RUN touch /var/www/html/database/database.sqlite

# Set proper permissions for Laravel storage, cache, and database
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Render injects PORT env variable
ENV PORT 80
EXPOSE 80

# Configure Apache port binding for Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Start script: ensure APP_KEY, run storage link, migrations, seeders, and start Apache
CMD touch /var/www/html/database/database.sqlite && \
    chmod -R 777 /var/www/html/database && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database && \
    php artisan key:generate --force && \
    php artisan storage:link || true && \
    php artisan migrate --force && \
    php artisan db:seed --force || true && \
    apache2-foreground
