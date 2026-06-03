# ZithengeMart - C2C E-Commerce Platform

A Consumer-to-Consumer (C2C) e-commerce platform designed for the South African informal economy, enabling individual buyers and sellers to trade goods directly with one another in a secure, structured online environment.

## 🌐 Live Site
[https://zithengemart.infinityfreeme/index.php] (https://zithengemart.infinityfreeme.admin/)


## ✨ Features

### Customer Features
- User registration and secure login
- Post product listings with image upload
- Browse and search listings by keyword and category
- View product details with seller contact information
- Contact sellers via WhatsApp, Email or in-platform messaging
- Save items to a personal wishlist
- Manage profile and personal listings

### Admin Features
- Secure admin login with Role-Based Access Control (RBAC)
- Dashboard with live platform statistics
- Approve or remove product listings
- Ban/unban users and manage user roles
- Add and delete product categories

## 🛠️ Tech Stack

| Technology | Purpose |
|---|---|
| HTML5 | Page structure and content |
| CSS3 | Styling and responsive design |
| JavaScript | Interactive features and navigation |
| PHP 8.x | Server-side logic and session management |
| MySQL | Relational database |
| PDO | Secure database queries |

## 📁 Project Structure

## 🗄️ Database Setup

1. Create a MySQL database named `zithengemart_db`
2. Import the `zithengemart_db.sql` file
3. Copy `config/db.example.php` to `config/db.php`
4. Update `config/db.php` with your database credentials

## 🚀 Local Setup

1. Install [WAMP Server](https://www.wampserver.com)
2. Clone this repository into `C:\wamp64\www\`
3. Set up the database as described above
4. Access the site at `http://localhost/zithengemart/`
5. Access the admin panel at `http://localhost/zithengemart/admin/`

## 🔐 Admin Access

- URL: `/admin/login.php`
- Create an admin account by running the `create_admin.php` script once, then delete it

## 📱 Responsive Design

ZithengeMart is fully responsive and tested on:
- Desktop (1440px+)
- iPad Pro (1024px)
- iPad Air (820px)
- iPhone XR (414px)
- Mobile phones (375px)

## 📄 License

This project was developed for academic purposes.
