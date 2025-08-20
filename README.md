# Friends and Momos Restaurant Management System

## Project Overview

The Friends and Momos Restaurant Management System is a comprehensive web-based application designed to manage all aspects of a Nepalese restaurant business. The system includes customer-facing features for ordering and reservations, as well as a complete administrative panel for restaurant management.

## Features

### Customer Features
- **Homepage**: Attractive landing page with restaurant information
- **Menu Browsing**: Categorized menu with detailed item information
- **Online Ordering**: Shopping cart functionality with order placement
- **User Registration/Login**: Account management system
- **Order History**: Track past orders and reorder functionality
- **Reservations**: Table booking system with availability checking
- **User Dashboard**: Personal account management and order tracking

### Admin Features
- **Dashboard**: Real-time analytics and business metrics
- **Menu Management**: Complete CRUD operations for menu items and categories
- **Order Management**: Process and track orders through different stages
- **Reservation Management**: Manage table bookings and availability
- **Customer Management**: View and manage customer accounts
- **Reports**: Sales analytics and business intelligence
- **Settings**: Configure restaurant parameters and business rules

## Technology Stack

### Frontend
- **HTML5**: Semantic markup for all pages
- **CSS3**: Modern styling with CSS Grid and Flexbox
- **JavaScript**: Interactive functionality and AJAX calls
- **Responsive Design**: Mobile-first approach for all devices

### Backend
- **PHP 8.x**: Server-side programming
- **MySQL 8.x**: Database management
- **Custom MVC Architecture**: Organized code structure
- **PDO**: Database abstraction layer
- **Session Management**: User authentication and cart management

### Security Features
- **Password Hashing**: Secure password storage
- **CSRF Protection**: Cross-site request forgery prevention
- **SQL Injection Prevention**: Prepared statements
- **Input Validation**: Server-side data validation
- **Rate Limiting**: Login attempt protection
- **Session Security**: Secure session configuration

## Project Structure

```
friends_momo/
├── assets/
│   ├── css/
│   │   ├── style.css          # Main stylesheet
│   │   └── admin.css          # Admin panel styles
│   ├── js/
│   │   ├── main.js            # Main JavaScript functionality
│   │   └── admin.js           # Admin panel JavaScript
│   └── images/                # Image assets
├── config/
│   ├── database.php           # Database configuration
│   └── RestaurantConfig.php   # Application settings
├── controllers/
│   └── AuthController.php     # Authentication logic
├── models/
│   ├── BaseModel.php          # Base model class
│   ├── User.php               # User model
│   ├── Order.php              # Order model
│   ├── Reservation.php        # Reservation model
│   ├── Category.php           # Category model
│   └── MenuItem.php           # Menu item model
├── views/
│   ├── admin/
│   │   ├── dashboard.php      # Admin dashboard
│   │   └── menu.php           # Menu management
│   └── user/
│       └── dashboard.php      # User dashboard
├── includes/
│   ├── header.php             # Main site header
│   ├── footer.php             # Main site footer
│   ├── admin_header.php       # Admin panel header
│   └── admin_footer.php       # Admin panel footer
├── api/
│   └── cart.php               # Cart management API
├── database/
│   └── schema.sql             # Database schema
└── [HTML pages]               # Public-facing pages
```

## Database Schema

### Core Tables
- **users**: User account management
- **categories**: Menu categorization
- **menu_items**: Restaurant menu items
- **orders**: Customer orders
- **order_items**: Individual items within orders
- **reservations**: Table bookings
- **restaurant_tables**: Table management
- **settings**: System configuration

### Additional Tables
- **loyalty_points**: Customer loyalty program
- **reviews**: Customer feedback system
- **activity_logs**: Audit trail
- **notifications**: User notifications
- **coupons**: Discount management
- **coupon_usage**: Coupon tracking

## Installation Instructions

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx)
- Modern web browser

### Setup Steps

1. **Database Setup**
   ```sql
   -- Import the database schema
   mysql -u root -p < database/schema.sql
   ```

2. **Configuration**
   ```php
   // Update config/database.php with your database credentials
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'friends_momo');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

3. **File Permissions**
   ```bash
   chmod 755 assets/
   chmod 644 assets/css/*
   chmod 644 assets/js/*
   ```

4. **Default Admin Account**
   - Email: admin@friendsmomos.com
   - Password: admin123
   - (Change this after first login)

## Usage Guide

### Customer Workflow
1. **Browse Menu**: Customers can view categorized menu items
2. **Add to Cart**: Select items and quantities
3. **Register/Login**: Create account or sign in
4. **Place Order**: Complete order with contact information
5. **Track Order**: Monitor order status in user dashboard

### Admin Workflow
1. **Login**: Access admin panel with admin credentials
2. **Dashboard**: View business metrics and recent activity
3. **Menu Management**: Add/edit menu items and categories
4. **Order Processing**: Update order status and manage fulfillment
5. **Reports**: Generate sales and customer analytics

## API Endpoints

### Cart Management
- `POST /api/cart.php?action=add` - Add item to cart
- `POST /api/cart.php?action=remove` - Remove item from cart
- `POST /api/cart.php?action=update` - Update item quantity
- `GET /api/cart.php?action=get` - Get cart contents
- `GET /api/cart.php?action=count` - Get cart item count

### Authentication
- `POST /controllers/AuthController.php?action=login` - User login
- `POST /controllers/AuthController.php?action=register` - User registration
- `POST /controllers/AuthController.php?action=logout` - User logout

## Configuration Options

### Restaurant Settings
- Restaurant name, contact information
- Operating hours by day of week
- Tax rates and delivery fees
- Loyalty program parameters
- Reservation settings

### System Settings
- Timezone configuration
- Currency settings
- Email notifications
- Security parameters
- Feature toggles

## Security Considerations

### Authentication
- Password strength requirements (minimum 8 characters)
- Rate limiting on login attempts
- Remember me functionality with secure tokens
- Session timeout management

### Data Protection
- Input sanitization and validation
- SQL injection prevention with prepared statements
- XSS protection through output encoding
- CSRF token validation
- Secure session configuration

### Access Control
- Role-based permissions (customer, staff, admin)
- Admin panel access restrictions
- User data privacy protection

## Maintenance

### Regular Tasks
- Database backup (automated recommended)
- Log file rotation
- Cache clearing if implemented
- Security updates
- Performance monitoring

### Monitoring
- Error logging and review
- Database performance
- User activity tracking
- Order fulfillment metrics

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in config/database.php
   - Verify MySQL service is running
   - Confirm database exists and user has permissions

2. **Cart Not Working**
   - Ensure sessions are enabled in PHP
   - Check API endpoint accessibility
   - Verify JavaScript console for errors

3. **Admin Panel Access**
   - Confirm user role is 'admin'
   - Check session variables
   - Verify admin authentication logic

4. **Email Notifications**
   - Configure SMTP settings if using email
   - Check spam folders
   - Verify email addresses in settings

### Debug Mode
Enable debug mode by adding to config files:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## Performance Optimization

### Database
- Regular table optimization
- Index monitoring and optimization
- Query performance analysis
- Connection pooling consideration

### Frontend
- Image optimization
- CSS/JavaScript minification
- Browser caching headers
- CDN implementation for static assets

### Backend
- PHP opcode caching (OPcache)
- Session optimization
- Database connection optimization
- Memory usage monitoring

## Future Enhancements

### Planned Features
- Mobile app development
- Online payment integration
- Real-time order tracking
- Customer feedback system
- Inventory management
- Staff scheduling
- Multi-location support
- Advanced reporting dashboard

### Integration Possibilities
- Payment gateways (Stripe, PayPal)
- SMS notifications
- Email marketing platforms
- Accounting software
- POS system integration
- Third-party delivery services

## Support and Contribution

### Getting Help
- Review this documentation thoroughly
- Check the troubleshooting section
- Examine error logs for specific issues
- Test with debug mode enabled

### Contributing
- Follow existing code style and structure
- Test all changes thoroughly
- Update documentation for new features
- Consider security implications

## License and Credits

This restaurant management system was developed as a comprehensive solution for small to medium-sized restaurants. The codebase follows modern PHP development practices and includes security best practices suitable for production deployment.

### Technologies Used
- PHP 8.x
- MySQL 8.x
- HTML5/CSS3/JavaScript
- Chart.js for analytics visualization
- Font Awesome for icons

### Development Notes
- MVC architecture for maintainable code
- Responsive design for all devices
- Progressive enhancement approach
- Accessibility considerations
- SEO-friendly markup

For technical support or feature requests, please refer to the project documentation or contact the development team.
