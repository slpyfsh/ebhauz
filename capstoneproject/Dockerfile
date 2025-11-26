# 1. Base Image: Use an official PHP image with Apache pre-installed.
FROM php:8.2-apache

# 2. Install Required PHP Extensions
RUN docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# --- NEW STEP: Enable Apache Modules ---
# Ensure the rewrite module is enabled (good practice for clean URLs)
# and ensure the PHP module is explicitly configured (though it usually is).
RUN a2enmod rewrite \
    && service apache2 restart
# ---------------------------------------

# 3. Copy Application Code
# Copy all files from your project directory into the Apache web root.
COPY . /var/www/html/
