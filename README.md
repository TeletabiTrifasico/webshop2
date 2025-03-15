# Webshop Project

## Project Overview
A modern e-commerce application built with PHP, featuring a responsive design and real-time cart functionality. This webshop allows users to browse products, manage their shopping cart, and complete purchases.

## Features
- User authentication (login and registration)
- Product browsing and detailed view
- Real-time shopping cart management
- Responsive design for all devices
- Dynamic cart updates without page refresh
- Secure user sessions
- Profile management system
- Admin dashboard with order management
- Toast notifications for user feedback
- Order status tracking
- Image upload for products
- Role-based access control (admin/user)

## Technologies Used
- PHP 8.0+
- MySQL
- JavaScript (ES6)
- Bootstrap 5
- HTML5 & CSS3

## Directory Structure
```
webshop/
├── app/
│   ├── Controllers/
│   │   ├── AdminController.php
│   │   ├── ApiController.php
│   │   ├── AuthController.php
│   │   ├── CartController.php
│   │   ├── Controller.php
│   │   ├── HomeController.php
│   │   ├── ProductController.php
│   │   └── UserController.php
│   ├── Models/
│   │   ├── Model.php
│   │   ├── User.php
│   │   ├── Product.php
│   │   └── Order.php
│   └── views/
│       ├── layouts/
│       ├── admin/
│       ├── auth/
│       ├── cart/
│       ├── home/
│       ├── products/
│       └── user/
├── config/
│   ├── config.php
│   └── database.php
└── public/
    ├── css/
    ├── js/
    ├── images/
    │   └── products/
    └── index.php
```

## Installation
1. Clone the repository to your local machine
2. Configure your web server (Apache/Nginx) to point to the `public` directory
3. Create a MySQL database named `webshop_db`
4. Import the SQL script from the Database Setup section below
5. Ensure write permissions for `/public/images/products` directory
6. Start your web server
7. Access the application through your web browser

## Database Setup
Execute the following SQL script to set up and populate the database:

```sql
CREATE DATABASE IF NOT EXISTS webshop_db;
USE webshop_db;

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
```

Note: The default password for all sample users is 'password'

## Usage
### Customer Features
- Browse products on the homepage
- Register an account or login
- Add products to cart with real-time updates
- View and modify cart contents
- Complete purchase through checkout
- Manage profile information
- View order history

### Admin Features
- Manage products (add/edit/delete)
- Manage users (add/edit/delete)
- View and manage orders
- Update order statuses
- View dashboard statistics
- Monitor recent orders and users

## API Endpoints
The application provides JSON API endpoints for accessing data:

### Get All Products
```
GET /api/products
```
Returns a list of all products.

### Get Single Product
```
GET /api/products/{id}
```
Example:
```
GET /api/products/5
```
Returns details of a specific product.

### Get Cart Contents
```
GET /api/cart
```
Returns the current user's cart contents. Requires authentication.

## Security Features
- Password hashing
- SQL injection protection through PDO
- XSS protection
- CSRF protection
- Secure session handling
- Role-based access control
- Secure file upload handling
- Input validation and sanitization

## Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License
MIT License