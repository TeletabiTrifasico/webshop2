-- Create tables
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert sample users (password: 'password' for all users)
INSERT INTO users (username, email, password, role) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('admin', 'admin@webshop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample products
INSERT INTO products (name, description, price, image) VALUES
('Classic Watch', 'A beautiful classic analog watch with leather strap', 199.99, '/images/watch1.jpg'),
('Smart Watch', 'Modern smartwatch with health tracking features', 299.99, '/images/watch2.jpg'),
('Gold Ring', '18K gold ring with diamond', 999.99, '/images/ring1.jpg'),
('Silver Necklace', 'Sterling silver necklace with pendant', 149.99, '/images/necklace1.jpg'),
('PlayStation 5', 'Latest gaming console from Sony', 499.99, '/images/ps5.jpg'),
('Xbox Series X', 'Microsoft''s powerful gaming console', 499.99, '/images/xbox.jpg'),
('Nintendo Switch', 'Portable gaming console', 299.99, '/images/switch.jpg'),
('Vintage Clock', 'Antique wall clock from 1950s', 399.99, '/images/clock1.jpg');

-- Insert sample orders
INSERT INTO orders (user_id, total_amount, status) VALUES
(1, 699.98, 'completed'),
(2, 499.99, 'pending'),
(1, 299.99, 'processing');

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 199.99),
(1, 2, 1, 499.99),
(2, 5, 1, 499.99),
(3, 7, 1, 299.99);