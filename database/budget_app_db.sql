-- ============================================================
-- Budget Management Application Database
-- Database Name: budget_app_db
-- ============================================================

CREATE DATABASE IF NOT EXISTS budget_app_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE budget_app_db;

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    user_id     INT          NOT NULL,
    name        VARCHAR(100) NOT NULL,
    type        ENUM('income','expense') NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: transactions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    id               INT            AUTO_INCREMENT PRIMARY KEY,
    user_id          INT            NOT NULL,
    category_id      INT            NOT NULL,
    transaction_type ENUM('income','expense') NOT NULL,
    amount           DECIMAL(15,2)  NOT NULL,
    description      TEXT,
    date             DATE           NOT NULL,
    created_at       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_txn_user FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    CONSTRAINT fk_txn_cat  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Default categories (inserted after first user registration
-- via application logic; these are sample seeds for reference)
-- ------------------------------------------------------------
-- Income categories  : Salary, Business, Freelance, Investment, Other Income
-- Expense categories : Transport, Food, Utilities, Entertainment, Savings,
--                      Healthcare, Education, Rent, Other Expense
