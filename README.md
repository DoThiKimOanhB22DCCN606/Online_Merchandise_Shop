````markdown
# Online Merchandise Shop

A full-stack e-commerce web platform built with PHP, MySQL, Bootstrap, the PayPal Checkout SDK, and PHPMailer. The repository provides a responsive customer-facing storefront alongside a comprehensive administrative dashboard for managing products, categories, promotional deals, customer reviews, and user accounts.

---

## Table of Contents

- [Features](#features)
  - [Storefront](#storefront)
  - [Admin Panel](#admin-panel)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Prerequisites](#prerequisites)
- [Installation & Setup](#installation--setup)
- [Configuration](#configuration)
  - [Database](#database)
  - [PayPal Checkout](#paypal-checkout)
  - [Email & SMTP Settings](#email--smtp-settings)
- [License](#license)

---

## Features

### Storefront

- **Product Browsing & Search:** Search items, filter by categories, and view product details.
- **Promotions & Highlights:** Integrated daily deals slider and bestseller listings.
- **Shopping Cart & Checkout:** Dynamic item quantity adjustments, total price calculation, and cart management.
- **Payment Integration:** Secure checkout workflow powered by the **PayPal Checkout REST SDK**.
- **Customer Authentication:** Registration, login, profile updates, and order history tracking.
- **Password Recovery:** Automated password reset workflow using **PHPMailer**.
- **Reviews & Feedback:** Write and submit ratings and product reviews.
- **Contact Support:** Contact form integrated with automated email notifications.

### Admin Panel

- **Dashboard Overview:** Centralized analytics and summary metrics.
- **Product Catalog CRUD:** Create, edit, list, and delete inventory items.
- **Category Management:** Add and organize merchandise categories.
- **Deals & Discounts:** Configure and manage daily deals and promotional banners.
- **Order Processing:** Track order statuses, view shipping information, and manage fulfillment.
- **Review Moderation:** Approve or remove user-submitted product reviews.
- **User Management:** Manage customer accounts and administrative roles.
- **Site Settings:** Global configuration panel for store-wide settings.

---

## Tech Stack

- **Backend:** PHP
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, SCSS, JavaScript, jQuery, Bootstrap 4, OwlCarousel
- **Dependency Manager:** Composer
- **Key Packages:**
  - `paypal/paypal-checkout-sdk`: PayPal REST API client
  - `phpmailer/phpmailer`: Email handling via SMTP/OAuth

---

## Project Structure

```text
├── admin/                         # Admin panel pages and dashboard logic
│   ├── includes/                  # Admin authentication guards, navigation, and database configuration
│   ├── upload/                    # Uploaded media assets (products, deals, logos)
│   ├── category.php               # Category management
│   ├── dashboard.php              # Administrative metrics
│   ├── manage_deals.php           # Deal configurations
│   ├── manage_orders.php          # Order management
│   ├── manage_reviews.php         # Review moderation
│   ├── post.php                   # Product inventory listings
│   ├── settings.php               # System settings
│   └── users.php                  # User account management
├── css/                           # Frontend CSS stylesheets
├── database/                      # SQL schema and seed files
│   └── db_merchshop.sql           # Database dump
├── functions/                     # Global utility functions
│   └── functions.php              # Global PHP utility functions
├── includes/                      # Frontend partials (navigation, footers, headers)
│   └── config.php                 # Database connection configuration
├── js/                            # Client-side JavaScript
├── javascript/                    # Additional client-side scripts and vendor UI libraries
├── lib/                           # Vendor UI tools (OwlCarousel, jQuery Easing)
├── mail/                          # Contact form mail scripts
├── scss/                          # Bootstrap and custom SCSS source files
├── vendor/                        # Composer dependencies (PHPMailer, PayPal SDK)
├── cart.php                       # Shopping cart page
├── checkout.php                   # Order checkout screen
├── forgot_password.php            # Password recovery request page
├── handle_review.php              # Review submission handler
├── index.php                      # Main landing page
├── order_history.php              # Customer order history
├── paypal_processor.php           # PayPal transaction handling
├── product.php                    # Product catalog page
├── reset_password.php             # Password reset screen
├── verify_paypal.php              # PayPal payment verification handler
└── viewdetail.php                 # Product details view
````

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
