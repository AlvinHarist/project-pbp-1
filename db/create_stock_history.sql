-- Migration: create stock_history table
-- Run this once to create the stock_history table used to store inventory changes.
CREATE TABLE IF NOT EXISTS `stock_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_buku` VARCHAR(100) NOT NULL,
  `change_qty` INT NOT NULL,
  `reason` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`id_buku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
