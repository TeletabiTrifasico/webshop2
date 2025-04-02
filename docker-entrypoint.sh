#!/bin/bash

echo "Starting WebShop application..."

# Check if JWT class exists
if [ -f "/var/www/html/app/Utils/JWT.php" ]; then
  echo "JWT class found."
else
  echo "ERROR: JWT class not found! This will cause authentication issues."
  ls -la /var/www/html/app/Utils/
fi

# Build frontend assets
if [ ! -d "/var/www/html/public/dist" ] || [ -z "$(ls -A /var/www/html/public/dist)" ]; then
    echo "Building frontend assets..."
    cd /var/www/html && npm install && npm run build
fi

# Fix permissions
echo "Setting correct permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
mkdir -p /var/www/html/public/images/products
chmod -R 777 /var/www/html/public/images/products

echo "WebShop is ready to serve!"

# Execute the command passed to docker run (usually apache2-foreground)
exec "$@"