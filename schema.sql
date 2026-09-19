-- Utang System — Customer Credit Management — Database Schema
-- Run this once to set up the database.
-- NOTE: if your host already created the database for you (e.g. via cPanel),
-- delete the CREATE DATABASE and USE lines below before importing.

CREATE DATABASE IF NOT EXISTS utang_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE utang_system;

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NULL,
    address VARCHAR(255) NULL,
    current_balance DECIMAL(10,3) NOT NULL DEFAULT 0.000,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE credit_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    type ENUM('charge', 'payment') NOT NULL,
    amount DECIMAL(10,3) NOT NULL,
    items_description TEXT NULL,
    balance_after DECIMAL(10,3) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX (customer_id),
    INDEX (created_at)
) ENGINE=InnoDB;

-- Single store-owner login. Password is hashed with PHP's password_hash() — see setup notes in README.md.
CREATE TABLE store_owner (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;
