# 🌾 AgriOrder — Cooperative Order Management System

A small but complete PHP + MySQL web application for managing supplier registrations, orders, auto-computed totals, and printable receipts. Built for cooperatives and agricultural aggregators.

---

## 📋 Features

| # | Feature | Description |
|---|---------|-------------|
| 1 | **Supplier Registration** | Name, phone, village/location, cooperative |
| 2 | **Order Creation** | Supplier, quantity (kg/sacks), unit price, pickup location |
| 3 | **Auto-Computed Totals** | JavaScript calculates total instantly as you type |
| 4 | **Printable Receipts** | Receipt page with Print button (print-friendly CSS) |
| 5 | **MySQL Storage** | All data persisted with prepared statements (no SQL injection) |
| 6 | **Dashboard** | Today's orders, today's value, recent 5 orders, total suppliers |
| 7 | **Login System** | Admin and Aggregator roles, bcrypt passwords |

---

## 🏗️ Project Structure (MVC)

```
oms/
├── config/
│   ├── auth.php          # Session & role helpers
│   └── database.php      # DB connection (edit credentials here)
├── models/
│   ├── OrderModel.php    # All order DB logic
│   ├── SupplierModel.php # All supplier DB logic
│   └── UserModel.php     # Login + user creation
├── views/
│   └── partials/
│       ├── header.php    # Shared nav + HTML head
│       └── footer.php    # Shared footer + JS
├── public/
│   ├── css/style.css     # Full design system
│   └── js/app.js         # Auto-total, print, UX helpers
├── index.php             # Home page
├── login.php             # Login form
├── logout.php            # Destroys session
├── dashboard.php         # Stats overview
├── orders.php            # Order CRUD + receipts
├── suppliers.php         # Supplier CRUD
├── users.php             # User management (admin only)
└── setup.sql             # Database schema + seed admin user
```

---

## ⚡ Local Setup

### Prerequisites
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.4+
- A web server (Apache/Nginx) or PHP's built-in server

### Steps

1. **Clone / download** this folder to your web root (e.g. `htdocs/oms` or `/var/www/html/oms`)

2. **Create the database**:
   ```bash
   mysql -u root -p < setup.sql
   ```
   Or import `setup.sql` via **phpMyAdmin**.

3. **Edit credentials** in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_mysql_username');
   define('DB_PASS', 'your_mysql_password');
   define('DB_NAME', 'order_mgmt');
   ```

4. **Run with PHP built-in server** (for local testing):
   ```bash
   cd oms
   php -S localhost:8000
   ```
   Then open `http://localhost:8000/login.php`

5. **Default login**:
   - Username: `admin`
   - Password: `admin123`
   - ⚠️ Change this immediately after first login via Users → Add New User

---

## 🌐 Free Hosting Options (PHP + MySQL)

### Option 1: InfinityFree *(Recommended for beginners)*
1. Sign up at [infinityfree.net](https://infinityfree.net)
2. Create a new hosting account
3. Go to **MySQL Databases** → create a database and user
4. Open **File Manager** → upload all files to `htdocs/`
5. Import `setup.sql` via **phpMyAdmin** in the control panel
6. Update `config/database.php` with the credentials from step 3
7. Visit your assigned subdomain, e.g. `http://yoursite.infinityfreeapp.com`

### Option 2: 000webhost
1. Sign up at [000webhost.com](https://www.000webhost.com)
2. Create a website → go to **Manage Website**
3. Under **Databases**, create a MySQL database
4. Upload files via **File Manager** → `public_html/`
5. Import SQL via phpMyAdmin
6. Update DB credentials in `config/database.php`

### Option 3: Render (via PHP container)
1. Sign up at [render.com](https://render.com)
2. Create a new **Web Service** from your GitHub repo
3. Set runtime to **Docker** or use a `render.yaml` with PHP Apache image
4. Add a **MySQL** or **PostgreSQL** add-on (note: you'd need to adjust queries for PostgreSQL)
5. Set environment variables for DB credentials instead of hardcoding them

### Option 4: Railway
1. Sign up at [railway.app](https://railway.app)
2. New project → **Deploy from GitHub**
3. Add a **MySQL** plugin to your project
4. Set environment variables: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`
5. Update `config/database.php` to use `getenv()` for credentials:
   ```php
   define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
   ```

---

## 🔒 Security Notes

- All database queries use **prepared statements** — no raw string interpolation
- Passwords are hashed with **bcrypt** via PHP's `password_hash()`
- Session IDs are regenerated on login to prevent session fixation
- HTML output is escaped with `htmlspecialchars()` via the `e()` helper
- Delete actions are **admin-only**; aggregators can only create and edit

---

## 🛠️ Customisation Tips

- **Currency**: Search for `RWF` in `orders.php` and `dashboard.php` to change to your local currency
- **Units**: Edit the `ENUM('kg','sacks')` in `setup.sql` and the `<select>` in `orders.php`
- **Branding**: Change `🌾 AgriOrder` in `header.php` and `login.php`
- **Colors**: Edit CSS variables at the top of `public/css/style.css`

---

*Built with plain PHP, MySQL, and a lot of care — no frameworks required.*
