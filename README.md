# Online Merchandise Shop

A full-stack e-commerce web application built with PHP, MySQL, Bootstrap, PayPal Checkout SDK, and PHPMailer. The platform features a responsive storefront for customers and a comprehensive administrative dashboard for catalog, order, user, and deal management.

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
* **Product Catalog & Browsing:** Browse products by category, filter items, view details, and search across inventory.
* **Promotions & Deals:** Daily deals carousel, bestsellers showcase, and promotional banners[cite: 1].
* **Shopping Cart & Checkout:** Add/remove items, adjust quantities, and dynamic total calculation[cite: 1].
* **Payment Integration:** Secure checkout powered by the **PayPal Checkout REST SDK**[cite: 1].
* **User Authentication & Profiles:** User registration, login, profile management, and order history tracking[cite: 1].
* **Password Recovery:** Automated reset password workflow via **PHPMailer**[cite: 1].
* **Reviews & Ratings:** Submit product reviews and feedback[cite: 1].
* **Contact & Inquiries:** Interactive contact form with automated email dispatch[cite: 1].

### Admin Panel
* **Admin Dashboard:** High-level summary of store activity and metrics[cite: 1].
* **Product Management:** Full CRUD (Create, Read, Update, Delete) operations for store listings and inventory[cite: 1].
* **Category Management:** Create and modify product categories[cite: 1].
* **Deals & Discounts:** Configure and manage daily deals and promotional offers[cite: 1].
* **Order Management:** View orders, inspect customer shipping details, and track fulfillment status[cite: 1].
* **Customer Review Moderation:** Approve and manage submitted customer feedback[cite: 1].
* **User Administration:** Manage registered customer accounts and role permissions[cite: 1].
* **Store Settings:** Customize site configurations and global store metadata[cite: 1].

---

## Tech Stack

* **Backend:** PHP (7.4+ / 8.x)[cite: 1]
* **Database:** MySQL / MariaDB[cite: 1]
* **Frontend:** HTML5, CSS3, SCSS, JavaScript, jQuery, Bootstrap 4, OwlCarousel[cite: 1]
* **Dependency Manager:** Composer[cite: 1]
* **Third-Party Libraries:**
  * `paypal/paypal-checkout-sdk`: PayPal REST API integration[cite: 1]
  * `phpmailer/phpmailer`: Transactional email delivery[cite: 1]

---

## Project Structure

```text
Online_Merchandise_Shop/
├── admin/                     # Admin control panel and management scripts[cite: 1]
│   ├── includes/              # Admin-specific navigation, config, and security guards[cite: 1]
│   ├── upload/                # Uploaded product and banner media[cite: 1]
│   ├── dashboard.php          # Main admin overview[cite: 1]
│   ├── post.php               # Product inventory management[cite: 1]
│   ├── manage_orders.php      # Order tracking[cite: 1]
│   └── ...
├── css/                       # Stylesheets and compiled CSS[cite: 1]
├── database/
│   └── db_merchshop.sql       # Database schema and initial seed data[cite: 1]
├── functions/
│   └── functions.php          # Global helper functions[cite: 1]
├── includes/                  # Common frontend components (navbars, footers, sidebars)[cite: 1]
├── js/ / javascript/          # Frontend script files and plugins[cite: 1]
├── lib/                       # Vendor assets (OwlCarousel, jQuery easing)[cite: 1]
├── mail/                      # PHPMailer handlers and contact form logic[cite: 1]
├── scss/                      # Bootstrap and custom SCSS source files[cite: 1]
├── vendor/                    # Composer dependencies[cite: 1]
├── cart.php                   # Shopping cart page[cite: 1]
├── checkout.php               # Checkout workflow[cite: 1]
├── index.php                  # Homepage[cite: 1]
├── paypal_processor.php       # PayPal order creation and capture handler[cite: 1]
├── product.php                # Product catalog view[cite: 1]
└── viewdetail.php             # Product details view[cite: 1]

---

## Prerequisites

* **PHP** 7.4 or higher (PHP 8.x supported)
* **MySQL Server** or **MariaDB**
* **Composer** (PHP dependency manager)
* **Local web server environment** (e.g., Apache/Nginx, XAMPP, WAMP, or Laragon)

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

* Create a new database in MySQL/MariaDB named `db_merchshop`.
* Import the schema file located at:

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

* `includes/config.php` — Storefront
* `admin/includes/config.php` — Admin panel

### PayPal Checkout

Provide your PayPal API credentials (Client ID and Secret) in the payment processor scripts:

* `paypal_processor.php`
* `verify_paypal.php`

> **Security note:** Do not commit PayPal credentials or other sensitive secrets directly to the repository. Use environment variables or another secure configuration mechanism where possible.

### Email & SMTP Settings

Configure your SMTP host, port, username, and password for transactional emails in:

* `handle_forgot_password.php` — Password reset emails
* `mail/contact.php` — Contact form inquiries

> **Security note:** Do not commit SMTP passwords, OAuth credentials, or other sensitive email credentials to the repository.

---

## License

This project is licensed under the MIT License.

```
```
