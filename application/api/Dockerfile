# Base image: Use an official PHP image with Apache (for Render.com service).
# This gives us PHP 8.1 and a production-ready web server.
FROM php:8.1-apache

# Set the main working directory for the container
WORKDIR /var/www/html/

# --- The Monorepo Fix ---
# 1. Copy the entire monorepo into the container.
# The container now has /var/www/html/library, /var/www/html/application, etc.
COPY . /var/www/html/

# 2. Install Composer globally
# We pull this from the official Composer image. It's a clean, pro-grade move.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. CD into the API directory *inside the container*
WORKDIR /var/www/html/application/api

# 4. Run composer install from here.
# The path "../../library" in your composer.json resolves to /var/www/html/library.
RUN composer install --no-dev --optimize-autoloader

# 5. Set the Apache document root to the API's public folder.
# Document Root: It ensures no one can access your composer.json or vendor/
# folder from the web. Note: The path is relative to the *original* WORKDIR
# (/var/www/html).
RUN sed -i 's!/var/www/html!/var/www/html/application/api/public!g' /etc/apache2/sites-available/000-default.conf

# 6. We don't need a "Start Command".
# The 'php:8.1-apache' image automatically starts the Apache server for us.
# This is more robust than the 'php -S' dev server.

