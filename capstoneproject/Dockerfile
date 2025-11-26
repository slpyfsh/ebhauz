# 1. Base Image: Use an official PHP image with Apache pre-installed.
# This simplifies setup by providing a web server that's ready to go.
FROM php:8.2-apache

# 2. Install Required PHP Extensions
# The 'mysqli' extension is crucial for your database connection.
RUN docker-php-ext-install mysqli \
    && docker-php-ext-enable mysqli

# 3. Copy Application Code
# Copy all files from your project directory (where the Dockerfile is)
# into the web root directory of the Apache server.
# The `connection.php` and all other PHP files should be here.
COPY . /var/www/html/

# 4. Set Environment Variables (Optional but good practice)
# Use environment variables for configuration if needed.
# Render automatically handles the PORT environment variable.

# The default Apache configuration is usually sufficient, and it
# runs on port 80 inside the container. Render automatically maps this.

# No CMD is needed because the base image already has the command
# to start the Apache server in the foreground.