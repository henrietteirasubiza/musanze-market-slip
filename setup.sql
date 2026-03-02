-- ==============================================
-- setup.sql — run this once to create all tables
-- Import via phpMyAdmin or run: mysql -u root -p < setup.sql
-- ==============================================

CREATE DATABASE IF NOT EXISTS order_mgmt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE order_mgmt;

-- Users table (login system)
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(80)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,           -- stored as bcrypt hash
    role       ENUM('admin','aggregator') NOT NULL DEFAULT 'aggregator',
    full_name  VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name    VARCHAR(150) NOT NULL,
    phone_number     VARCHAR(30)  NOT NULL,
    village_location VARCHAR(150) NOT NULL,
    cooperative_name VARCHAR(150),
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id      INT          NOT NULL,
    quantity         DECIMAL(10,2) NOT NULL,
    unit             ENUM('kg','sacks') NOT NULL DEFAULT 'kg',
    unit_price       DECIMAL(10,2) NOT NULL,
    total_amount     DECIMAL(12,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    pickup_location  VARCHAR(200) NOT NULL,
    created_by       INT,                        -- references users.id
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by)  REFERENCES users(id)     ON DELETE SET NULL
);

-- Seed a default admin account
-- Password: admin123  (you MUST change this after first login)
INSERT IGNORE INTO users (username, password, role, full_name)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'System Administrator'
);
