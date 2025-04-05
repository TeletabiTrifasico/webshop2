FROM php:8.0-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    curl \
    gnupg

# Add NodeSource repository and install Node.js 18.x
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Configure and install PHP extensions - make sure mysqli and pdo_mysql are included
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql zip

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create directory structure for autoloading
RUN mkdir -p /var/www/html/App/Utils
RUN mkdir -p /var/www/html/App/Middleware
RUN mkdir -p /var/www/html/App/Controllers/Api
RUN mkdir -p /var/www/html/App/Models

# Create symbolic links for case-insensitive paths
RUN if [ -d "/var/www/html/app" ] && [ ! -d "/var/www/html/App" ]; then \
    cp -R /var/www/html/app/* /var/www/html/App/; \
    fi

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies (if composer.json exists)
RUN if [ -f "composer.json" ]; then composer install --no-interaction; fi

# Install npm dependencies and build frontend
RUN NODE_OPTIONS=--max_old_space_size=4096 npm install && npm run build || echo "Build failed, but continuing..."

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Configure Apache document root
RUN sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf

# Create a PHP info file for testing
RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/info.php

# Apache environment setup - Create a custom PHP configuration file for environment variables
RUN echo "<?php\n\
putenv('DB_HOST=db');\n\
putenv('DB_NAME=webshop_db');\n\
putenv('DB_USER=webshopadmin');\n\
putenv('DB_PASSWORD=!webshopadmin2025');\n\
?>" > /var/www/html/public/env.php

# Expose port 80
EXPOSE 80

CMD ["apache2-foreground"]