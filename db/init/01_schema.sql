-- SmartStock schema (MySQL 8). Runs automatically on first start of the db volume.
SET NAMES utf8mb4;

CREATE TABLE roles (
  role_id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(role_id)
) ENGINE=InnoDB;

CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  expiry_alert_days INT NOT NULL DEFAULT 7
) ENGINE=InnoDB;

CREATE TABLE products (
  product_id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  sku_code VARCHAR(50) NOT NULL UNIQUE,
  unit_of_measure VARCHAR(30) NOT NULL,
  min_stock_level DECIMAL(10,2) NOT NULL DEFAULT 0,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB;

CREATE TABLE batches (
  batch_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  batch_number VARCHAR(100) NOT NULL,
  initial_quantity DECIMAL(10,2) NOT NULL,
  current_quantity DECIMAL(10,2) NOT NULL,
  manufacture_date DATE NULL,
  expiry_date DATE NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_batches_product FOREIGN KEY (product_id) REFERENCES products(product_id),
  UNIQUE KEY uq_product_batch (product_id, batch_number),
  KEY idx_fefo (product_id, expiry_date)
) ENGINE=InnoDB;

CREATE TABLE stock_transactions (
  transaction_id INT AUTO_INCREMENT PRIMARY KEY,
  batch_id INT NOT NULL,
  user_id INT NOT NULL,
  transaction_type VARCHAR(50) NOT NULL,
  quantity_changed DECIMAL(10,2) NOT NULL,
  remarks TEXT,
  transaction_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tx_batch FOREIGN KEY (batch_id) REFERENCES batches(batch_id),
  CONSTRAINT fk_tx_user FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE demand_forecasts (
  forecast_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  forecast_date DATE NOT NULL,
  predicted_demand_qty DECIMAL(10,2) NOT NULL,
  predicted_spoilage_qty DECIMAL(10,2) NOT NULL,
  recommended_reorder_qty DECIMAL(10,2) NOT NULL,
  confidence_score DECIMAL(5,2),
  generated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_forecast_product FOREIGN KEY (product_id) REFERENCES products(product_id)
) ENGINE=InnoDB;

CREATE TABLE alerts (
  alert_id INT AUTO_INCREMENT PRIMARY KEY,
  alert_type VARCHAR(20) NOT NULL,
  product_id INT NOT NULL,
  batch_id INT NULL,
  message VARCHAR(255) NOT NULL,
  is_resolved BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  resolved_at TIMESTAMP NULL,
  CONSTRAINT fk_alert_product FOREIGN KEY (product_id) REFERENCES products(product_id),
  CONSTRAINT fk_alert_batch FOREIGN KEY (batch_id) REFERENCES batches(batch_id),
  KEY idx_alert_lookup (alert_type, product_id, batch_id, is_resolved),
  KEY idx_alert_open (is_resolved, created_at)
) ENGINE=InnoDB;
