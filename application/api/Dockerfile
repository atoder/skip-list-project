# Base image: Use an official PHP image with Apache (most reliable for Render)
FROM php:8.1-apache

# Set working directory inside the container
WORKDIR /var/www/html/

# Copy the entire API directory into the container
COPY . /var/www/html/

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Execute composer install (installs symlink to the library)
RUN composer install --no-dev --optimize-autoloader

# Set document root to the public folder
# This makes the URL work without /public/index.php
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Render exposes $PORT, but this uses Apache's default port 80, which Render maps automatically.
# We don't need a custom start command for Apache.

