DROP DATABASE IF EXISTS tikika_db;
CREATE DATABASE tikika_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tikika_db;

-- Roles
CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Users
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role_id INT UNSIGNED NOT NULL DEFAULT 3,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30),
  is_verified TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Categories (e.g. Music, Tech, Conference)
CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Events
CREATE TABLE events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  organizer_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME,
  status ENUM('draft','published','cancelled') NOT NULL DEFAULT 'draft',
  capacity INT UNSIGNED DEFAULT NULL,
  category_id INT UNSIGNED,
  image_url VARCHAR(255) DEFAULT NULL, -- store path/URL of the event image
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE tickets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  event_id INT UNSIGNED NOT NULL,
  type VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  quantity INT UNSIGNED NOT NULL DEFAULT 0,
  sold INT UNSIGNED NOT NULL DEFAULT 0,
  available INT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

DELIMITER $$
CREATE TRIGGER tickets_before_insert
BEFORE INSERT ON tickets
FOR EACH ROW
BEGIN
  SET NEW.available = IF(NEW.quantity - NEW.sold < 0, 0, NEW.quantity - NEW.sold);
END$$

CREATE TRIGGER tickets_before_update
BEFORE UPDATE ON tickets
FOR EACH ROW
BEGIN
  SET NEW.available = IF(NEW.quantity - NEW.sold < 0, 0, NEW.quantity - NEW.sold);
END$$
DELIMITER ;


-- Orders
CREATE TABLE orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  event_id INT UNSIGNED NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  payment_provider VARCHAR(100),
  provider_reference VARCHAR(255),
  status ENUM('pending','paid','cancelled','refunded') NOT NULL DEFAULT 'pending',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
  FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Order items (tickets inside an order)
CREATE TABLE order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  ticket_id INT UNSIGNED NOT NULL,
  quantity INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE RESTRICT
);

-- Sessions (if you want DB-based sessions instead of PHP default files)
CREATE TABLE sessions (
  id VARCHAR(128) PRIMARY KEY,
  user_id INT UNSIGNED,
  data TEXT,
  last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Indexes
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_events_start ON events(start_datetime);
CREATE INDEX idx_tickets_event ON tickets(event_id);
CREATE INDEX idx_orders_user ON orders(user_id);