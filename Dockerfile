FROM php:8.3-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files (this will happen automatically with the volume mount)

# Configure Apache to use the right document root
RUN sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html/' /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html
