#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
until mysql -h db -u "$DB_USER" -p"$DB_PASSWORD" -e "SELECT 1" &> /dev/null
do
  echo "MySQL is not ready yet..."
  sleep 1
done
echo "MySQL is ready!"

# Check if cart_items table exists
echo "Checking database tables..."
if ! mysql -h db -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" -e "DESCRIBE cart_items" &> /dev/null; then
  echo "Creating cart_items table..."
  mysql -h db -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" <<EOF
  CREATE TABLE IF NOT EXISTS cart_items (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT NOT NULL,
      product_id INT NOT NULL,
      quantity INT NOT NULL DEFAULT 1,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );
EOF
  echo "Cart_items table created!"
fi

# Execute Apache in foreground
echo "Starting Apache..."
exec apache2-foreground