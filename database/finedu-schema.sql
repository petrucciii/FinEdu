-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versione server:              8.2.0 - MySQL Community Server - GPL
-- S.O. server:                  Win64
-- HeidiSQL Versione:            12.12.0.7122
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dump della struttura del database finedu
CREATE DATABASE IF NOT EXISTS `finedu` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `finedu`;

-- Dump della struttura di tabella finedu.analysts_consensus
CREATE TABLE IF NOT EXISTS `analysts_consensus` (
  `analysis_id` int NOT NULL AUTO_INCREMENT,
  `isin` varchar(12) NOT NULL,
  `firm_id` int NOT NULL,
  `date` date NOT NULL,
  `rating_id` int NOT NULL,
  `target_price` decimal(8,2) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`analysis_id`),
  KEY `rating_id` (`rating_id`),
  KEY `isin` (`isin`),
  KEY `firm_id` (`firm_id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.answers
CREATE TABLE IF NOT EXISTS `answers` (
  `id_answer` int NOT NULL AUTO_INCREMENT,
  `answer` varchar(255) NOT NULL,
  `id_question` int NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_answer`),
  KEY `id_question` (`id_question`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.board_members
CREATE TABLE IF NOT EXISTS `board_members` (
  `member_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(50) NOT NULL,
  `picture_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`member_id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.companies
CREATE TABLE IF NOT EXISTS `companies` (
  `isin` varchar(12) NOT NULL,
  `name` varchar(50) NOT NULL,
  `website` varchar(255) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `ea_code` int NOT NULL,
  `main_exchange` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`isin`),
  KEY `main_exchange` (`main_exchange`),
  KEY `country_code` (`country_code`),
  KEY `ea_code` (`ea_code`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.companies_board
CREATE TABLE IF NOT EXISTS `companies_board` (
  `isin` varchar(12) NOT NULL,
  `member_id` int NOT NULL,
  `role` varchar(60) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  PRIMARY KEY (`isin`,`member_id`),
  KEY `member_id` (`member_id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.companies_news
CREATE TABLE IF NOT EXISTS `companies_news` (
  `news_id` int NOT NULL,
  `isin` varchar(12) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  PRIMARY KEY (`news_id`,`isin`),
  KEY `isin` (`isin`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.companies_shareholders
CREATE TABLE IF NOT EXISTS `companies_shareholders` (
  `isin` varchar(12) NOT NULL,
  `firm_id` int NOT NULL,
  `ownership` decimal(4,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  PRIMARY KEY (`firm_id`,`isin`),
  KEY `isin` (`isin`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.completed_lessons
CREATE TABLE IF NOT EXISTS `completed_lessons` (
  `user_id` int NOT NULL,
  `id_lesson` int NOT NULL,
  `attempt` int NOT NULL,
  `completed` tinyint(1) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT (now()),
  PRIMARY KEY (`user_id`,`id_lesson`,`attempt`),
  KEY `id_lesson` (`id_lesson`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.countries
CREATE TABLE IF NOT EXISTS `countries` (
  `country_code` varchar(2) NOT NULL,
  `country` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`country_code`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.currencies
CREATE TABLE IF NOT EXISTS `currencies` (
  `currency_code` varchar(3) NOT NULL,
  `description` varchar(10) NOT NULL,
  `symbol` varchar(1) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`currency_code`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.data
CREATE TABLE IF NOT EXISTS `data` (
  `year` int NOT NULL,
  `isin` varchar(12) NOT NULL,
  `type_id` int NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `revenues` bigint DEFAULT NULL,
  `amortizations_depretiations` bigint DEFAULT NULL,
  `income_taxes` bigint DEFAULT NULL,
  `interests` bigint DEFAULT NULL,
  `net_profit` bigint DEFAULT NULL,
  `net_debt` bigint DEFAULT NULL,
  `share_number` bigint DEFAULT NULL,
  `free_cash_flow` bigint DEFAULT NULL,
  `capex` bigint DEFAULT NULL,
  `dividends` bigint DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  PRIMARY KEY (`isin`,`year`),
  KEY `type_id` (`type_id`),
  KEY `currency_code` (`currency_code`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.data_type
CREATE TABLE IF NOT EXISTS `data_type` (
  `type_id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(1) NOT NULL,
  `name` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`type_id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.exchanges
CREATE TABLE IF NOT EXISTS `exchanges` (
  `mic` varchar(7) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `short_name` varchar(10) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `opening_hour` time DEFAULT NULL,
  `closing_hour` time DEFAULT NULL,
  `currency_code` varchar(3) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`mic`),
  KEY `country_code` (`country_code`),
  KEY `currency_code` (`currency_code`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.explanations
CREATE TABLE IF NOT EXISTS `explanations` (
  `id_explanation` int NOT NULL AUTO_INCREMENT,
  `id_lesson` int NOT NULL,
  `body` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_explanation`),
  KEY `id_lesson` (`id_lesson`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.firms
CREATE TABLE IF NOT EXISTS `firms` (
  `firm_id` int NOT NULL AUTO_INCREMENT,
  `firm_name` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`firm_id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.lessons
CREATE TABLE IF NOT EXISTS `lessons` (
  `id_lesson` int NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `hint` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `id_module` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_lesson`),
  KEY `id_module` (`id_module`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.levels
CREATE TABLE IF NOT EXISTS `levels` (
  `level_id` int NOT NULL AUTO_INCREMENT,
  `level` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`level_id`),
  KEY `fk_levels_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.listings
CREATE TABLE IF NOT EXISTS `listings` (
  `ticker` varchar(5) NOT NULL,
  `mic` varchar(7) NOT NULL,
  `isin` varchar(12) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ticker`,`mic`),
  KEY `mic` (`mic`),
  KEY `isin` (`isin`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.modules
CREATE TABLE IF NOT EXISTS `modules` (
  `id_module` int NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_module`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.news
CREATE TABLE IF NOT EXISTS `news` (
  `news_id` int NOT NULL AUTO_INCREMENT,
  `newspaper_id` int NOT NULL,
  `headline` varchar(255) NOT NULL,
  `subtitle` varchar(255) NOT NULL,
  `body` blob NOT NULL,
  `author` varchar(50) NOT NULL,
  `date` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`news_id`),
  KEY `id_user` (`id_user`),
  KEY `newspaper_id` (`newspaper_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.newspapers
CREATE TABLE IF NOT EXISTS `newspapers` (
  `newspaper_id` int NOT NULL AUTO_INCREMENT,
  `newspaper` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`newspaper_id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `ticker` varchar(5) NOT NULL,
  `mic` varchar(7) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `buyPrice` decimal(8,2) NOT NULL,
  `sellPrice` decimal(8,2) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT (now()),
  `portfolio_id` int NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`order_id`),
  KEY `portfolio_id` (`portfolio_id`),
  KEY `ticker` (`ticker`,`mic`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.portfolios
CREATE TABLE IF NOT EXISTS `portfolios` (
  `portfolio_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `inital_liquidity` int NOT NULL DEFAULT '10000',
  `liquidity` int NOT NULL DEFAULT '0',
  `invested` int NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  PRIMARY KEY (`portfolio_id`),
  KEY `user_id` (`user_id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.prices
CREATE TABLE IF NOT EXISTS `prices` (
  `price_id` int NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT (now()),
  `ticker` varchar(5) NOT NULL,
  `mic` varchar(7) NOT NULL,
  `price` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`price_id`),
  KEY `ticker` (`ticker`,`mic`)
) ENGINE=InnoDB AUTO_INCREMENT=5548 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.questions
CREATE TABLE IF NOT EXISTS `questions` (
  `id_question` int NOT NULL AUTO_INCREMENT,
  `id_lesson` int NOT NULL,
  `experience` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_question`),
  KEY `id_lesson` (`id_lesson`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.ratings
CREATE TABLE IF NOT EXISTS `ratings` (
  `rating_id` int NOT NULL AUTO_INCREMENT,
  `rating` varchar(40) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`rating_id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`role_id`),
  KEY `fk_roles_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.sectors
CREATE TABLE IF NOT EXISTS `sectors` (
  `ea_code` int NOT NULL,
  `description` varchar(200) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ea_code`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

-- Dump della struttura di tabella finedu.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(70) NOT NULL,
  `password` varchar(255) NOT NULL,
  `experience` int NOT NULL DEFAULT '0',
  `level_id` int NOT NULL DEFAULT '1',
  `role_id` int NOT NULL DEFAULT '2',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT (now()),
  `last_update` datetime DEFAULT NULL,
  `id_user_updated` int DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  KEY `level_id` (`level_id`),
  KEY `id_user_updated` (`id_user_updated`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- L’esportazione dei dati non era selezionata.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
