# Use PHP 8.0 Apache image
FROM php:8.0-apache

LABEL authors="Hugo Jimenez Barrasa"

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl

# Install Node.js and npm (use a newer version)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy app files first
COPY app /var/www/html/app/
COPY config /var/www/html/config/
COPY public /var/www/html/public/

# Copy frontend files
COPY Resources /var/www/html/Resources/
COPY package.json package-lock.json* /var/www/html/

# Copy extra files
COPY composer.json composer.lock* /var/www/html/
COPY .env* /var/www/html/
COPY vite.config.js /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/public/images/products \
    && chmod -R 777 /var/www/html/public/images/products

# Copy Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Enable the site configuration
RUN a2ensite 000-default.conf

# Set environment variables for Node.js
ENV NODE_OPTIONS="--max-old-space-size=2048"
ENV NODE_ENV="production"

# Expose port 80
EXPOSE 80

# Add a startup script to build assets at container runtime
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set the entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]