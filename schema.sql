-- CA Office Management System Database Schema
-- MySQL

CREATE DATABASE IF NOT EXISTS `pinnacle_office` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pinnacle_office`;

-- 1. Clients Table
CREATE TABLE IF NOT EXISTS `clients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Employees Table
CREATE TABLE IF NOT EXISTS `employees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Invoices Table
CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoiceNumber` VARCHAR(50) NOT NULL UNIQUE,
    `taskId` INT NOT NULL,
    `customerName` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `plan` VARCHAR(100) NOT NULL,
    `pdfPath` VARCHAR(255) DEFAULT NULL,
    `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tasks Table
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `orderId` VARCHAR(50) NOT NULL UNIQUE,
    `client` VARCHAR(100) NOT NULL,
    `customerName` VARCHAR(255) NOT NULL,
    `pan` VARCHAR(20) DEFAULT '',
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `plan` VARCHAR(100) NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `taxExpert` VARCHAR(100) NOT NULL,
    `status` ENUM('Pending', 'Completed', 'Stuck') NOT NULL DEFAULT 'Pending',
    `remarks` TEXT DEFAULT NULL,
    `reference` VARCHAR(255) DEFAULT '',
    `completedAt` DATETIME DEFAULT NULL,
    `invoiceId` INT DEFAULT NULL,
    `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updatedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `idx_tasks_client` (`client`),
    KEY `idx_tasks_taxexpert` (`taxExpert`),
    KEY `idx_tasks_status` (`status`),
    CONSTRAINT `fk_tasks_invoice` FOREIGN KEY (`invoiceId`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add Constraint on invoices pointing back to tasks (after both exist)
ALTER TABLE `invoices` ADD CONSTRAINT `fk_invoices_task` FOREIGN KEY (`taskId`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;
