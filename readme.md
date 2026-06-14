# BudgetManager — PHP + MySQL Budget Management Application

A full-featured web application for tracking income, expenses, categories,
and generating financial reports. Built with PHP 8+ and MySQL.

---

## Requirements

| Tool       | Version  |
|------------|----------|
| PHP        | 8.0+     |
| MySQL      | 5.7+ / MariaDB 10.3+ |
| Web server | Apache (XAMPP / WAMP / LAMP) or Nginx |

---

## Quick Start (XAMPP — recommended for local development)

### Step 1 — Copy files to web root

```
C:\xampp\htdocs\budget-app\        (Windows)
/opt/lampp/htdocs/budget-app/      (Linux)
/Applications/XAMPP/htdocs/budget-app/  (macOS)
```

Extract the full `budget-app/` folder so the path is correct.

### Step 2 — Create the database

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open your browser and go to: `http://localhost/phpmyadmin`
3. Click **Import** → **Choose File** → select `database/budget_app_db.sql`
4. Click **Go**.

This creates the database `budget_app_db` and all tables.

### Step 3 — Configure the database connection

Open `config/database.php` and set your credentials:

```php
define('DB_HOST',  'localhost');
define('DB_NAME',  'budget_app_db');   // ← database name
define('DB_USER',  'root');            // ← your MySQL username
define('DB_PASS',  '');                // ← your MySQL password (blank for XAMPP default)
define('BASE_URL', 'http://localhost/budget-app'); // ← adjust if needed
```

### Step 4 — Open the app

```
http://localhost/budget-app/auth/register.php
```

Register your account — default income and expense categories are seeded automatically.

---

## Folder Structure

```
budget-app/
├── config/
│   └── database.php        ← DB credentials & PDO connection
├── includes/
│   ├── functions.php       ← Shared helper functions
│   ├── header.php          ← Navigation & page head
│   └── footer.php          ← Footer & scripts
├── auth/
│   ├── login.php           ← User login
│   ├── register.php        ← User registration
│   └── logout.php          ← Session destroy
├── income/
│   ├── index.php           ← List income (search, filter, paginate)
│   ├── create.php          ← Add income record
│   ├── edit.php            ← Update income record
│   └── delete.php          ← Delete income record
├── expenses/
│   ├── index.php           ← List expenses
│   ├── create.php          ← Add expense
│   ├── edit.php            ← Update expense
│   └── delete.php          ← Delete expense
├── categories/
│   ├── index.php           ← List categories (income + expense)
│   ├── create.php          ← Add category
│   ├── edit.php            ← Update category
│   └── delete.php          ← Delete category
├── profile/
│   ├── index.php           ← View profile & stats
│   └── edit.php            ← Update name, email, password
├── reports/
│   └── index.php           ← Monthly summaries, category analysis, trend
├── assets/
│   ├── css/style.css       ← Full responsive stylesheet
│   └── js/main.js          ← Interactivity (nav toggle, confirm delete, etc.)
├── database/
│   └── budget_app_db.sql   ← Database schema (import this)
└── index.php               ← Dashboard
```

---

## Database Schema

**Database name:** `budget_app_db`

| Table          | Description                                    |
|----------------|------------------------------------------------|
| `users`        | User accounts (id, name, email, password)      |
| `categories`   | Income/expense categories (linked to user)     |
| `transactions` | All income & expense records (linked to user + category) |

---

## Features

- **Authentication** — Register, login, logout with password hashing
- **Income CRUD** — Create, list (with filter/search/pagination), edit, delete
- **Expense CRUD** — Full CRUD identical to income
- **Category CRUD** — Per-user income & expense categories; delete protection
- **Dashboard** — Total income, expenses, balance; monthly overview; recent transactions; 6-month trend
- **Reports** — Monthly income/expense summary, category-wise analysis, 12-month trend table
- **Profile** — View account, update name/email/password
- **Security** — CSRF tokens on all forms, PDO prepared statements, session management

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| White page / DB error | Check credentials in `config/database.php` |
| "404 Not Found" | Ensure folder is at `htdocs/budget-app/` and `BASE_URL` matches |
| "Access denied for user 'root'" | Set correct MySQL password in `DB_PASS` |
| Categories missing | They are auto-seeded on registration; re-register or insert manually |
| Images / CSS not loading | Verify `BASE_URL` matches your actual URL |
