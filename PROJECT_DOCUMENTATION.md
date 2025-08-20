# Friends and Momos - Complete Restaurant Management System

## Project Overview
A comprehensive PHP and MySQL-based restaurant management system for "Friends and Momos" - specializing in authentic Himalayan/Nepalese cuisine. This system provides a complete digital experience including online ordering, table reservations, user management, and administrative controls.

## Technology Stack
- **Backend**: PHP 8.x with MVC Architecture
- **Database**: MySQL 8.x
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Custom PHP MVC with Bootstrap 5
- **Session Management**: PHP Sessions
- **Security**: Password hashing, SQL injection prevention, CSRF protection

## Enhanced Features

### 🍽️ **Customer Features**
1. **User Authentication System**
   - Registration with email verification
   - Secure login/logout
   - Password reset functionality
   - Profile management

2. **Interactive Menu System**
   - Dynamic menu display from database
   - Category-based filtering
   - Real-time price updates
   - Item availability status

3. **Shopping Cart & Ordering**
   - Add/remove items with quantity control
   - Cart persistence across sessions
   - Order history tracking
   - Order status updates

4. **Table Reservation System**
   - Real-time table availability
   - Date/time selection
   - Special requests handling
   - Reservation confirmation emails

5. **Payment Processing**
   - Multiple payment method support
   - Order summary and receipts
   - Payment history tracking

### 🔧 **Administrative Features**
1. **Dashboard Analytics**
   - Sales reports and charts
   - Popular items analysis
   - Customer analytics
   - Revenue tracking

2. **Menu Management**
   - Add/edit/delete menu items
   - Category management
   - Price updates
   - Image upload system

3. **Order Management**
   - Real-time order tracking
   - Order status updates
   - Kitchen display system
   - Delivery management

4. **Reservation Management**
   - Table availability calendar
   - Reservation approval/rejection
   - Customer communication

5. **User Management**
   - Customer account management
   - Staff role assignments
   - Activity logs

## Database Schema

### Tables Structure

#### Users Table
```sql
- id (Primary Key)
- first_name
- last_name
- email (Unique)
- password (Hashed)
- phone
- address
- role (customer/admin/staff)
- created_at
- updated_at
- email_verified
- verification_token
```

#### Categories Table
```sql
- id (Primary Key)
- name
- description
- image
- sort_order
- is_active
- created_at
```

#### Menu Items Table
```sql
- id (Primary Key)
- category_id (Foreign Key)
- name
- description
- price
- image
- is_available
- preparation_time
- calories
- spice_level
- is_vegetarian
- created_at
- updated_at
```

#### Orders Table
```sql
- id (Primary Key)
- user_id (Foreign Key)
- order_number
- status (pending/confirmed/preparing/ready/delivered/cancelled)
- total_amount
- payment_status
- payment_method
- delivery_address
- special_instructions
- created_at
- updated_at
```

#### Order Items Table
```sql
- id (Primary Key)
- order_id (Foreign Key)
- menu_item_id (Foreign Key)
- quantity
- unit_price
- total_price
- special_notes
```

#### Reservations Table
```sql
- id (Primary Key)
- user_id (Foreign Key)
- table_number
- guest_count
- reservation_date
- reservation_time
- status (pending/confirmed/cancelled/completed)
- special_requests
- created_at
- updated_at
```

#### Tables Table
```sql
- id (Primary Key)
- table_number
- capacity
- location
- is_available
- description
```

## Project Structure

```
friends_momo_php/
├── config/
│   ├── database.php
│   ├── config.php
│   └── constants.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navigation.php
│   └── functions.php
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   ├── admin.css
│   │   └── responsive.css
│   ├── js/
│   │   ├── main.js
│   │   ├── cart.js
│   │   └── admin.js
│   ├── images/
│   │   ├── menu/
│   │   ├── gallery/
│   │   └── icons/
│   └── uploads/
├── controllers/
│   ├── AuthController.php
│   ├── MenuController.php
│   ├── CartController.php
│   ├── OrderController.php
│   ├── ReservationController.php
│   ├── AdminController.php
│   └── PaymentController.php
├── models/
│   ├── User.php
│   ├── MenuItem.php
│   ├── Category.php
│   ├── Order.php
│   ├── Reservation.php
│   └── Database.php
├── views/
│   ├── public/
│   │   ├── index.php
│   │   ├── menu.php
│   │   ├── cart.php
│   │   ├── login.php
│   │   ├── register.php
│   │   ├── reservation.php
│   │   ├── about.php
│   │   ├── contact.php
│   │   └── profile.php
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── menu_management.php
│   │   ├── orders.php
│   │   ├── reservations.php
│   │   ├── users.php
│   │   └── reports.php
│   └── email/
│       ├── registration.php
│       ├── order_confirmation.php
│       └── reservation_confirmation.php
├── api/
│   ├── auth.php
│   ├── cart.php
│   ├── orders.php
│   └── reservations.php
├── database/
│   ├── migrations/
│   │   ├── 001_create_users_table.sql
│   │   ├── 002_create_categories_table.sql
│   │   ├── 003_create_menu_items_table.sql
│   │   ├── 004_create_orders_table.sql
│   │   ├── 005_create_order_items_table.sql
│   │   ├── 006_create_reservations_table.sql
│   │   └── 007_create_tables_table.sql
│   ├── seeds/
│   │   ├── categories.sql
│   │   ├── menu_items.sql
│   │   └── admin_user.sql
│   └── setup.sql
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── robots.txt
├── vendor/ (Composer dependencies)
├── logs/
├── composer.json
├── README.md
└── .env
```

## Enhanced UI/UX Features

### Design System
- **Color Palette**: 
  - Primary: #8b7d6b (Warm brown)
  - Secondary: #6d5f4d (Dark brown)
  - Accent: #e6d3f7 (Light purple)
  - Success: #4CAF50
  - Warning: #FF9800
  - Error: #F44336

- **Typography**: 
  - Headers: 'Poppins', sans-serif
  - Body: 'Inter', sans-serif
  - Code: 'Fira Code', monospace

- **Components**:
  - Modern card-based layouts
  - Smooth animations and transitions
  - Loading states and skeletons
  - Toast notifications
  - Modal dialogs
  - Progress indicators

### Responsive Design
- Mobile-first approach
- Tablet and desktop optimizations
- Touch-friendly interfaces
- Accessible navigation
- Progressive Web App (PWA) features

## Security Features
1. **Authentication Security**
   - Password hashing with bcrypt
   - CSRF token protection
   - Session management
   - Rate limiting for login attempts

2. **Data Protection**
   - SQL injection prevention
   - XSS protection
   - Input validation and sanitization
   - Secure file uploads

3. **Admin Security**
   - Role-based access control
   - Activity logging
   - Secure admin routes
   - Two-factor authentication (optional)

## Performance Optimizations
- Database query optimization
- Image compression and lazy loading
- CSS/JS minification
- Caching strategies
- CDN integration ready

## Development Phases

### Phase 1: Foundation (Current)
- [x] Project structure setup
- [x] Database schema design
- [x] Basic MVC framework
- [ ] User authentication system

### Phase 2: Core Features
- [ ] Menu management system
- [ ] Shopping cart functionality
- [ ] Order processing
- [ ] Reservation system

### Phase 3: Advanced Features
- [ ] Payment integration
- [ ] Admin dashboard
- [ ] Reporting system
- [ ] Email notifications

### Phase 4: Enhancement
- [ ] UI/UX improvements
- [ ] Performance optimization
- [ ] Security hardening
- [ ] Testing and deployment

## Installation Requirements
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Composer for dependency management
- Node.js (for asset compilation)

## Getting Started
1. Clone the repository
2. Install PHP dependencies: `composer install`
3. Configure database settings in `.env`
4. Run database migrations
5. Seed initial data
6. Configure web server
7. Access the application

This documentation will be updated as the project evolves.
