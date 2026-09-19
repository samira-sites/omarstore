# Customer Credit (Tab) Management System

A small PHP + MySQL app for tracking customer credit at a grocery store: who owes what, purchase history, and payments — built for use at a counter on a phone or tablet.

## What it does

- **Dashboard** — total outstanding credit, how many customers owe money, top 5 highest balances
- **Customer list** — search by name/phone, balances shown at a glance (red = owing)
- **Customer page** — full charge/payment history with running balance, quick "Add charge" and "Record payment" buttons
- **Statement view** — printable/shareable page per customer for disputes or records
- Balances are recalculated automatically and safely (using a database transaction) every time a charge or payment is logged

## Requirements

- PHP 8.0+ with PDO MySQL extension
- MySQL or MariaDB
- Any standard shared host with cPanel works fine

## Setup

1. **Create the database.** In phpMyAdmin (or `mysql` CLI), run everything in `schema.sql`. This creates the `tab_system` database and its three tables.

2. **Configure the connection.** Copy `config.php.example` to `config.php` and fill in your real database host, name, username, and password:
   ```
   cp config.php.example config.php
   ```
   `config.php` is already in `.gitignore` so it won't get committed if you push this to GitHub.

3. **Upload everything** to your cPanel hosting via FTP or the File Manager (e.g. into `public_html/tab/`).

4. **Create your login.** Visit `yoursite.com/tab/setup_owner.php` in the browser once, pick a username and password. This is the single login your friend will use to access the system.

5. **Delete `setup_owner.php`** from the server immediately after — leaving it up would let anyone reset the login.

6. Go to `yoursite.com/tab/login.php` and log in.

## File structure

```
tab-system/
├── schema.sql              — run once to create the database
├── config.php.example      — copy to config.php and fill in credentials
├── setup_owner.php         — one-time login creation (delete after use)
├── login.php / logout.php
├── index.php                — dashboard
├── customers.php            — customer list + search
├── customer_add.php         — add a new customer
├── customer_view.php        — one customer's balance + history
├── charge_add.php           — log a new purchase on credit
├── payment_add.php          — log a payment toward balance
├── statement.php            — printable statement per customer
├── includes/
│   ├── db.php                — PDO connection
│   ├── auth.php               — login/session/CSRF helpers
│   ├── functions.php          — data access + balance logic
│   ├── header.php / footer.php
└── assets/css/style.css
```

## Notes

- All database queries use prepared statements — safe from SQL injection.
- Every form checks a CSRF token before writing to the database.
- Amounts use 3 decimal places to match KWD (fils), but this works fine for any currency — just relabel if needed.
- This is single-user (one shared login for the store owner). If your friend wants staff accounts with separate logins later, the `store_owner` table can be extended to support multiple rows plus a `created_by` column on transactions.
- No online payment processing here — this only tracks credit given and payments received in person (cash, transfer, etc.).
