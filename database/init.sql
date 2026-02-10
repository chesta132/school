-- ChardyMart POS Database Schema
-- Created: 2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Database: chardymart_pos

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `categories`
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `products`
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `weight` int(11) NOT NULL COMMENT 'in grams/ml',
  `sku` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) NOT NULL DEFAULT 0.00 COMMENT 'percentage',
  `stock` int(11) NOT NULL DEFAULT 0,
  `sold` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `transactions`
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_code` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `total_discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_code` (`transaction_code`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `transaction_items`
CREATE TABLE IF NOT EXISTS `transaction_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount` decimal(5,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_id` (`transaction_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Insert Sample Data
-- --------------------------------------------------------

-- Insert default user (password: password)
INSERT INTO `users` (`username`, `email`, `password`) VALUES
('admin', 'admin@chardymart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert categories
INSERT INTO `categories` (`name`, `code`) VALUES
('Minuman', 'MIN'),
('Makanan', 'MAK'),
('Snack', 'SNK'),
('Alat Tulis', 'ATK'),
('Kesehatan', 'KES');

-- Insert sample products
INSERT INTO `products` (`name`, `category_id`, `weight`, `sku`, `price`, `discount`, `stock`, `sold`) VALUES
-- Minuman
('Coca Cola', 1, 330, 'MIN-COC-330', 6000.00, 10.00, 50, 25),
('Aqua Botol', 1, 600, 'MIN-AQU-600', 3500.00, 0.00, 100, 45),
('Teh Botol Sosro', 1, 450, 'MIN-TEH-450', 4500.00, 5.00, 75, 30),
('Fanta Orange', 1, 330, 'MIN-FAN-330', 5500.00, 10.00, 40, 15),
-- Makanan
('Indomie Goreng', 2, 85, 'MAK-IND-85', 3000.00, 0.00, 150, 80),
('Mie Sedaap Kari', 2, 90, 'MAK-MIE-90', 3200.00, 0.00, 120, 60),
('Roti Tawar Sari Roti', 2, 250, 'MAK-ROT-250', 12000.00, 5.00, 25, 10),
-- Snack
('Chitato Rasa Sapi Panggang', 3, 68, 'SNK-CHI-68', 10000.00, 0.00, 60, 35),
('Oreo Vanila', 3, 137, 'SNK-ORE-137', 8500.00, 10.00, 8, 20),
('Tango Wafer Coklat', 3, 47, 'SNK-TAN-47', 2500.00, 0.00, 90, 45),
-- Alat Tulis
('Pulpen Standard AE7', 4, 10, 'ATK-PUL-10', 3000.00, 0.00, 100, 50),
('Buku Tulis Sinar Dunia 58 Lembar', 4, 150, 'ATK-BUK-150', 5000.00, 0.00, 80, 40),
('Penghapus Steadtler', 4, 20, 'ATK-PEN-20', 4000.00, 5.00, 50, 25),
-- Kesehatan
('Masker Sensi 3 Ply', 5, 50, 'KES-MAS-50', 15000.00, 0.00, 35, 18),
('Hand Sanitizer 100ml', 5, 100, 'KES-HAN-100', 12000.00, 10.00, 45, 22);
