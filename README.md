# Online Merchandise Shop

A full-stack e-commerce web application built with PHP, MySQL, Bootstrap, the PayPal Checkout SDK, and PHPMailer. The platform features a responsive storefront for customers and a comprehensive administrative dashboard for catalog, order, user, and deal management.

---

## Table of Contents

- [Features](#features)
  - [Customer Storefront](#customer-storefront)
  - [Admin Panel](#admin-panel)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Prerequisites](#prerequisites)
- [Installation & Setup](#installation--setup)
- [Configuration](#configuration)
- [License](#license)

---

## Features

### Customer Storefront

- **Product Catalog & Browsing:** Browse products by category, filter items, view details, and search across inventory.
- **Promotions & Deals:** Daily deals carousel, bestsellers showcase, and promotional banners.
- **Shopping Cart & Checkout:** Add/remove items, adjust quantities, and dynamically calculate the total.
- **Payment Integration:** Secure checkout powered by the **PayPal Checkout SDK**.
- **User Authentication & Profiles:** User registration, login, profile management, and order history tracking.
- **Password Recovery:** Automated password reset workflow via **PHPMailer**.
- **Reviews & Ratings:** Submit product reviews and feedback.
- **Contact & Inquiries:** Interactive contact form with automated email dispatch.

### Admin Panel

- **Admin Dashboard:** High-level summary of store activity and metrics.
- **Product Management:** Full CRUD (Create, Read, Update, Delete) operations for store listings and inventory.
- **Category Management:** Create and modify product categories.
- **Deals & Discounts:** Configure and manage daily deals and promotional offers.
- **Order Management:** View orders, inspect customer shipping details, and track fulfillment status.
- **Customer Review Moderation:** Approve and manage submitted customer feedback.
- **User Administration:** Manage registered customer accounts and role permissions.
- **Store Settings:** Customize site configurations and global store metadata.

---

## Tech Stack

- **Backend:** PHP 7.4+ / 8.x
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, SCSS, JavaScript, jQuery, Bootstrap 4, OwlCarousel
- **Dependency Manager:** Composer
- **Third-Party Libraries:**
  - `paypal/paypal-checkout-sdk`: PayPal REST API integration
  - `phpmailer/phpmailer`: Transactional email delivery

---

## Project Structure

```text
Online_Merchandise_Shop/
├── admin/                     # Admin control panel and management scripts
│   ├── includes/              # Admin-specific navigation, config, and security guards
│   ├── upload/                # Uploaded product and banner media
│   ├── dashboard.php          # Main admin overview
│   ├── post.php               # Product inventory management
│   ├── manage_orders.php      # Order tracking
│   └── ...
├── css/                       # Stylesheets and compiled CSS
├── database/
│   └── db_merchshop.sql       # Database schema and initial seed data
├── functions/
│   └── functions.php          # Global helper functions
├── includes/                  # Common frontend components (navbars, footers, sidebars)
├── js/ / javascript/          # Frontend script files and plugins
├── lib/                       # Vendor assets (OwlCarousel, jQuery easing)
├── mail/                      # PHPMailer handlers and contact form logic
├── scss/                      # Bootstrap and custom SCSS source files
├── vendor/                    # Composer dependencies
├── cart.php                   # Shopping cart page
├── checkout.php               # Checkout workflow
├── index.php                  # Homepage
├── paypal_processor.php       # PayPal order creation and capture handler
├── product.php                # Product catalog view
└── viewdetail.php             # Product details view
```

---

## Prerequisites

- **PHP** 7.4 or higher (PHP 8.x supported)
- **MySQL Server** or **MariaDB**
- **Composer** (PHP dependency manager)
- **Local web server environment** (e.g., Apache/Nginx, XAMPP, WAMP, or Laragon)

---

## Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/dothikimoanhb22dccn606/online_merchandise_shop.git
cd online_merchandise_shop
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Set Up the Database

- Create a new database in MySQL/MariaDB named `db_merchshop`.
- Import the schema file located at:

```text
database/db_merchshop.sql
```

### 4. Configure the Database Connection

Verify and update the database credentials in `includes/config.php` and `admin/includes/config.php`:

```php
<?php

$hostname = "localhost";
$username = "root";
$password = "";
$database = "db_merchshop";

$conn = mysqli_connect($hostname, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

### 5. Run the Application

Place the repository directory into your web server root (`htdocs`, `www`, etc.), or start PHP's built-in development server:

```bash
php -S localhost:8000
```

---

## Configuration

### Database

Update the database credentials in both configuration files:

- `includes/config.php` — Storefront
- `admin/includes/config.php` — Admin panel

### PayPal Checkout

Provide your PayPal API credentials (Client ID and Secret) in the payment processor scripts:

- `paypal_processor.php`
- `verify_paypal.php`

> **Security note:** Do not commit PayPal credentials or other sensitive secrets directly to the repository. Use environment variables or another secure configuration mechanism where possible.

### Email & SMTP Settings

Configure your SMTP host, port, username, and password for transactional emails in:

- `handle_forgot_password.php` — Password reset emails
- `mail/contact.php` — Contact form inquiries

> **Security note:** Do not commit SMTP passwords, OAuth credentials, or other sensitive email credentials to the repository.

---

## License

This project is licensed under the MIT License.
