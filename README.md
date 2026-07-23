# Busia Chicken Farm Website

Premium poultry e-commerce website with farm management system.

## 🐔 About

Busia Chicken Farm is a modern, production-ready website for a premium poultry business based in Busia, Kenya. The platform provides:

- **E-commerce store** for chickens, eggs, and feeds
- **Farm management dashboard** for tracking flocks, production, and finances
- **Admin panel** for managing products, orders, and reports
- **Responsive design** optimized for all devices
- **Premium UI** with smooth animations and micro-interactions

## 🚀 Features

### Public Features
- Full-screen hero slider with auto-advance
- Product catalog with categories (Broilers, Layers, Chicks, Feeds)
- Shopping cart with session management
- Checkout with Bank Transfer & Cash on Delivery
- Responsive mobile-first design
- SEO optimized pages

### Admin Features
- Product management (CRUD operations)
- Order processing with status tracking
- Financial records and reports
- Flock management system
- Production tracking (eggs, meat, mortality)
- Vaccination scheduling

### Technical Features
- PHP 8.2+ with strict typing
- MySQL 8.0+ with PDO
- Vanilla CSS with design system
- Vanilla JavaScript (ES6+)
- Premium animations and transitions
- Security: CSRF tokens, input validation, bcrypt hashing
- Database: 13 normalized tables with foreign keys

## 📦 Installation

### Requirements
- PHP 8.2 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server (or PHP built-in server for development)
- cPanel hosting (for production deployment)

### Local Development Setup

1. **Clone the repository**
```bash
git clone https://github.com/YOUR_USERNAME/busia-chicken-website.git
cd busia-chicken-website
```

2. **Set up the database**
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE busia_chicken_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema and sample data
php setup_database.php
```

3. **Configure database connection**
Edit `Backend/config/database.php` with your credentials:
```php
const DB_HOST = 'localhost';
const DB_NAME = 'busia_chicken_db';
const DB_USER = 'root';
const DB_PASS = 'your_password';
```

4. **Start development server**
```bash
php -S localhost:8000
```

5. **Access the website**
- Homepage: http://localhost:8000/Frontend/
- Shop: http://localhost:8000/Frontend/pages/shop.php
- Admin: http://localhost:8000/Frontend/admin/products.php

## 🗂️ Project Structure

```
busia-chicken-website/
├── Backend/
│   ├── api/                    # AJAX endpoints
│   │   ├── cart.php
│   │   ├── checkout.php
│   │   └── mpesa-callback.php
│   └── config/                 # Configuration files
│       ├── database.php        # Database connection
│       ├── queries.php         # Query helper functions
│       ├── schema.sql          # Database schema
│       └── security.php        # Security functions
├── Frontend/
│   ├── admin/                  # Admin panel pages
│   │   ├── orders.php
│   │   ├── products.php
│   │   ├── reports.php
│   │   └── settings.php
│   ├── assets/                 # Static assets
│   │   ├── css/
│   │   │   ├── style.css           # Design system
│   │   │   ├── components.css      # UI components
│   │   │   ├── animations.css      # Animations
│   │   │   └── responsive.css      # Media queries
│   │   └── js/
│   │       └── main.js             # Core JavaScript
│   ├── includes/               # Shared components
│   │   ├── config.php
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── seo.php
│   ├── pages/                  # Public pages
│   │   ├── about.php
│   │   ├── cart.php
│   │   ├── checkout.php
│   │   ├── contact.php
│   │   ├── dashboard.php
│   │   ├── faq.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── products.php
│   │   ├── register.php
│   │   └── shop.php
│   └── index.php               # Homepage
├── .htaccess                   # Apache configuration
├── setup_database.php          # Database setup script
└── README.md
```

## 🎨 Design System

### Color Palette
- **Primary**: `#1B5E20` (Forest Green)
- **Primary Light**: `#4CAF50` (Vibrant Green)
- **Accent**: `#FF8F00` (Amber/Gold)
- **Accent Light**: `#FFC107` (Golden Yellow)
- **Dark**: `#1A1A2E` (Deep Navy)

### Typography
- **Headings**: Outfit (Google Fonts)
- **Body**: Inter (Google Fonts)
- **Accents**: DM Sans (Google Fonts)

### Key Features
- 6 responsive breakpoints (mobile-first)
- 20+ animation types
- Premium shadows with depth
- Smooth cubic-bezier transitions
- Touch-friendly buttons (48px minimum)

## 🔒 Security

- CSRF token protection on all forms
- Prepared statements (PDO) for SQL injection prevention
- XSS prevention with htmlspecialchars
- Bcrypt password hashing (cost 12)
- Session security with httponly cookies
- Input validation and sanitization
- Rate limiting helpers

## 📊 Database Schema

13 tables including:
- `users` - Authentication and user data
- `products` - Product catalog
- `categories` - Product categories
- `orders` - Order management
- `order_items` - Order line items
- `flocks` - Poultry flock tracking
- `production_records` - Daily production logs
- `vaccinations` - Vaccination scheduling
- `financial_records` - Expense/income tracking
- And more...

## 🚀 Deployment (cPanel)

1. Upload files to `public_html/`
2. Create MySQL database via cPanel
3. Import schema: `php setup_database.php`
4. Update `Backend/config/database.php` with live credentials
5. Set up SSL certificate
6. Configure `.htaccess` for URL rewriting

## 📈 Performance

- Lighthouse Score Target: 90+
- LCP < 2.5s
- FCP < 1.5s
- CLS < 0.1
- GPU-safe animations (transform + opacity only)
- Lazy loading images
- Minified CSS/JS (production)

## 🤝 Contributing

This is a private commercial project. For issues or feature requests, please contact the development team.

## 📄 License

Proprietary - All rights reserved © 2026 Busia Chicken Farm

## 👨‍💻 Development

Built with:
- PHP 8.2+
- MySQL 8.0+
- Vanilla CSS3 (no frameworks)
- Vanilla JavaScript (ES6+)
- Premium design principles

Developed by: Kiro AI
Project Status: Production Ready
Version: 1.0.0

## 📞 Contact

- **Email**: info@busiachicken.com
- **Phone**: +254 727 585 599
- **Address**: Nasira AC Sub-location, Busibwabo, Busia County, Kenya

---

**Note**: Before deploying to production, ensure you update all credentials, add real product images, and configure email notifications.
