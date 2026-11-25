-- Municipal Service Finder
-- Fresh database setup for XAMPP / phpMyAdmin
-- Creates the database and all tables required by the current PHP project.
-- No sample LGU records and no default administrator/staff accounts are inserted.
--
-- How to use on another laptop:
-- 1. Start Apache and MySQL in XAMPP.
-- 2. Open http://localhost/phpmyadmin/
-- 3. Import this file (or paste its contents into the SQL tab).
-- 4. Ensure config.php uses DB_NAME = 'msf_db'.
--
-- If you want a different database name, change it in both the CREATE DATABASE
-- and USE statements below, then use the same name in config.php.

CREATE DATABASE IF NOT EXISTS `msf_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `msf_db`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(80) NOT NULL,
    `slug` VARCHAR(80) NOT NULL UNIQUE,
    `icon` VARCHAR(8) NOT NULL DEFAULT '📄',
    `description` VARCHAR(255) NOT NULL DEFAULT '',
    `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `offices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `description` VARCHAR(255) NOT NULL DEFAULT '',
    `location` VARCHAR(160) NOT NULL DEFAULT '',
    `floor` VARCHAR(40) NOT NULL DEFAULT '',
    `directions` TEXT,
    `phone` VARCHAR(60) NOT NULL DEFAULT '',
    `email` VARCHAR(120) NOT NULL DEFAULT '',
    `open_days` VARCHAR(20) NOT NULL DEFAULT '1,2,3,4,5',
    `open_time` TIME NULL,
    `close_time` TIME NULL,
    `hours_note` VARCHAR(255) NOT NULL DEFAULT '',
    `map_spot` VARCHAR(30) NOT NULL DEFAULT '',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `office_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `summary` VARCHAR(255) NOT NULL DEFAULT '',
    `description` TEXT,
    `who_can_apply` VARCHAR(255) NOT NULL DEFAULT '',
    `requirements` TEXT,
    `steps` TEXT,
    `fees` VARCHAR(255) NOT NULL DEFAULT '',
    `processing_time` VARCHAR(120) NOT NULL DEFAULT '',
    `keywords` VARCHAR(500) NOT NULL DEFAULT '',
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_verified` DATE NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_services_category` (`category_id`),
    INDEX `idx_services_office` (`office_id`),
    CONSTRAINT `fk_services_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
    CONSTRAINT `fk_services_office`
        FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `search_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `query` VARCHAR(255) NOT NULL,
    `results_count` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_search_logs_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
    `k` VARCHAR(40) NOT NULL PRIMARY KEY,
    `v` VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `staff` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `office_id` INT NULL,
    `full_name` VARCHAR(120) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_staff_office` (`office_id`),
    CONSTRAINT `fk_staff_office`
        FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `actor_type` ENUM('admin','staff') NOT NULL,
    `actor_id` INT NOT NULL,
    `office_id` INT NULL,
    `action` VARCHAR(80) NOT NULL,
    `entity_type` VARCHAR(80) NOT NULL,
    `entity_id` INT NULL,
    `details` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_activity_actor` (`actor_type`, `actor_id`),
    INDEX `idx_activity_office` (`office_id`),
    INDEX `idx_activity_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- End of setup.
-- For security, create the first administrator through a password-hashing setup
-- process in the PHP application. Do not store a plain-text password here.