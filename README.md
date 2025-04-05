# Webshop Project

## Project Overview
A modern e-commerce application built with PHP and Vue.js, featuring responsive design and real-time cart functionality. This webshop allows users to browse products, manage their shopping cart, and complete purchases.

## Instructions for Teacher
### How to Download and Run This Project

1. **Clone or download the repository**:
   ```bash
   git clone https://github.com/TeletabiTrifasico/webshop2
   # OR download the ZIP file and extract it
   ```

2. **Make sure Docker Desktop is installed and running** on your system
   - Download from: https://www.docker.com/products/docker-desktop/

3. **Navigate to the project directory**:
   ```bash
   cd webshop
   ```

4. **Build and start the Docker containers**:
   ```bash
   docker-compose up -d --build
   ```

5. **Access the application**:
   - Website: http://localhost:8088
   - Admin panel: http://localhost:8088/admin
   - Database: localhost:3307 (if you need direct access)

6. **Default Login Credentials**:
   - Admin User:
     - Email: admin@webshop.com
     - Password: password
   - Sample User:
     - Email: john@example.com
     - Password: password

7. **To stop the containers when finished**:
   ```bash
   docker-compose down
   ```

### Testing the Application Features

1. **As a Customer**:
   - Browse products on the homepage
   - Add items to cart (test real-time updates)
   - Checkout process (create an account first)
   - View order history

2. **As an Admin** (login with admin@webshop.com):
   - Add/edit/delete products (image upload supports JPG format only)
   - Manage users
   - View and update orders

## Features Implemented
- User authentication (login/registration)
- Product browsing with detailed view
- Real-time shopping cart management
- Checkout process with order confirmation
- Admin dashboard for product, user, and order management
- Responsive design (works on mobile/tablet/desktop)
- Image uploads for products (JPG only)
- Role-based access control (admin/user)

## Technologies Used
- Backend: PHP 8.0+
- Frontend: Vue.js, Bootstrap 5
- Database: MySQL 8.0
- Environment: Docker containerized setup
- Authentication: JWT token-based authentication

## API Endpoints

The application provides JSON API endpoints:

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `GET /api/auth/user` - Get current user information

### Products
- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get single product details
- `GET /api/products/latest` - Get latest products

### Cart
- `GET /api/cart` - Get cart contents
- `POST /api/cart` - Add item to cart
- `PUT /api/cart/{id}` - Update cart item
- `DELETE /api/cart/{id}` - Remove cart item
- `POST /api/cart/checkout` - Process checkout

### Admin
- `GET /api/admin/products` - List all products (admin)
- `POST /api/admin/products` - Create product (admin)
- `PUT /api/admin/products/{id}` - Update product (admin)
- `DELETE /api/admin/products/{id}` - Delete product (admin)
- `GET /api/admin/users` - List all users (admin)
- `POST /api/admin/users` - Create user (admin)
- `PUT /api/admin/users/{id}` - Update user (admin)
- `GET /api/admin/orders` - List all orders (admin)

## Notes
- The application follows MVC architecture on the backend
- All product images must be in JPG format
- The database automatically seeds with sample products and users
- The checkout process creates orders and clears the cart
- Admin functionality is protected with role-based authentication
