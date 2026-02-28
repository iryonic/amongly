-- Amongly Production Schema (v2 — fully synced with live DB)
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `amongly` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `amongly`;

-- Categories
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- Words
CREATE TABLE IF NOT EXISTS `words` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `word` VARCHAR(100) NOT NULL,
    `difficulty` ENUM('easy', 'medium', 'hard') DEFAULT 'easy',
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    INDEX `idx_cat_diff` (`category_id`, `difficulty`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- Rooms
CREATE TABLE IF NOT EXISTS `rooms` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_code` VARCHAR(10) UNIQUE NOT NULL,
    `host_id` INT DEFAULT NULL,
    `category_id` INT NOT NULL,
    `difficulty` ENUM('easy', 'medium', 'hard') DEFAULT 'easy',
    `max_players` INT DEFAULT 20,
    `status` ENUM('waiting', 'word_reveal', 'clue', 'decision', 'voting', 'resolving', 'reveal', 'finished') DEFAULT 'waiting',
    `current_round` INT DEFAULT 0,
    `phase_start_time` INT DEFAULT 0,
    `winner` ENUM('crew', 'imposter') DEFAULT NULL,
    `eliminated_player_id` INT DEFAULT NULL,
    `end_reason` VARCHAR(30) DEFAULT NULL,
    `last_activity_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_last_activity` (`last_activity_at`)
) ENGINE=InnoDB;

-- Players
CREATE TABLE IF NOT EXISTS `players` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_id` INT NOT NULL,
    `nickname` VARCHAR(50) NOT NULL,
    `avatar` VARCHAR(20) DEFAULT '👤',
    `is_host` BOOLEAN DEFAULT FALSE,
    `is_alive` BOOLEAN DEFAULT TRUE,
    `session_id` VARCHAR(255) NOT NULL,
    `last_active_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE,
    INDEX `idx_room_alive` (`room_id`, `is_alive`),
    INDEX `idx_session` (`session_id`)
) ENGINE=InnoDB;

-- Rounds
CREATE TABLE IF NOT EXISTS `rounds` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_id` INT NOT NULL,
    `word_id` INT NOT NULL,
    `imposter_id` INT NOT NULL,
    `status` ENUM('active', 'completed') DEFAULT 'active',
    `winner` ENUM('crew', 'imposter') DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`word_id`) REFERENCES `words`(`id`),
    FOREIGN KEY (`imposter_id`) REFERENCES `players`(`id`),
    INDEX `idx_room_status` (`room_id`, `status`)
) ENGINE=InnoDB;

-- Clues
CREATE TABLE IF NOT EXISTS `clues` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `round_id` INT NOT NULL,
    `player_id` INT NOT NULL,
    `clue_text` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`player_id`) REFERENCES `players`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_player_round` (`round_id`, `player_id`)
) ENGINE=InnoDB;

-- Votes (eliminate or skip)
CREATE TABLE IF NOT EXISTS `votes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `round_id` INT NOT NULL,
    `voter_id` INT NOT NULL,
    `voted_player_id` INT DEFAULT NULL,
    `vote_type` ENUM('eliminate','skip') NOT NULL DEFAULT 'eliminate',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`voter_id`) REFERENCES `players`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_voter_round` (`round_id`, `voter_id`),
    INDEX `idx_round_voter` (`round_id`, `voter_id`)
) ENGINE=InnoDB;

-- Imposter Guesses
CREATE TABLE IF NOT EXISTS `imposter_guesses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `round_id` INT NOT NULL,
    `player_id` INT NOT NULL,
    `guess` VARCHAR(100) NOT NULL,
    `is_correct` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`round_id`) REFERENCES `rounds`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`player_id`) REFERENCES `players`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_player_round` (`round_id`, `player_id`)
) ENGINE=InnoDB;

-- Admins
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
