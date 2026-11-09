# TechMoto PH — Multi-Vendor E-Commerce Platform

PHP + MySQL multi-vendor store for Computer Parts and Motorcycle Parts.
Roles: Admin, Customer, Seller, Affiliate. Payments: COD + GCash.

## Quick Install (XAMPP)

1. Copy `ecommerce` folder to `htdocs`
2. Start Apache + MySQL
3. Import `database.sql` in phpMyAdmin (creates DB `ecommerce_mv`)
4. Visit: http://localhost/ecommerce/setup_admin.php
5. Delete `setup_admin.php`
6. Open: http://localhost/ecommerce/

### Default Logins (after setup_admin.php)
- **Admin:** http://localhost/ecommerce/admin/login.php — `admin` / `Admin@12345`
- **Sellers:** seller1@techmoto.ph or seller2@techmoto.ph / `Seller@12345`
- **Customer:** Register at /user/register.php

### Config
Edit `config/database.php` if needed (DB_HOST, DB_NAME, DB_USER, DB_PASS, SITE_URL).

## Features
- Responsive storefront with Bootstrap 5
- Interactive Leaflet map for delivery location
- Cart, checkout (COD + GCash receipt upload)
- Seller registration with admin approval
- Affiliate referral codes and commissions
- Admin dashboard with Chart.js stats
- Secure auth (password_hash, CSRF, PDO)

## Structure
admin/ · user/ · seller/ · api/ · assets/ · config/ · includes/
