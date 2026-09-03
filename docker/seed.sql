-- Schema + a single demo login for the deployed container.
-- (Derived from database.sql, minus the sample pdf_uploads rows that pointed at
--  files which don't exist in a fresh container.)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `pdf_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `upload_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_path` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'processing',
  `score` int(11) DEFAULT 0,
  `summary` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `quiz_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(100) DEFAULT NULL,
  `quiz_name` varchar(255) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `total_questions` int(11) DEFAULT NULL,
  `percentage` int(11) DEFAULT NULL,
  `pdf_id` int(11) DEFAULT NULL,
  `taken_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `date_joined` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Demo login so you can sign in immediately:  email 2305457@kiit.ac.in  /  password 1234
INSERT INTO `users` (`name`, `email`, `password`, `role`)
VALUES ('Ishita', '2305457@kiit.ac.in', '1234', 'Student');
