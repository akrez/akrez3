-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2020 at 07:28 PM
-- Server version: 10.1.34-MariaDB
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `akrezir_log`
--

-- --------------------------------------------------------

--
-- Table structure for table `api`
--

DROP TABLE IF EXISTS `api`;
CREATE TABLE IF NOT EXISTS `api` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_version` varchar(5) DEFAULT NULL,
  `blog_name` varchar(31) DEFAULT NULL,
  `created_date` varchar(11) DEFAULT NULL,
  `created_time` varchar(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_agent` varchar(2047) DEFAULT NULL,
  `ip` varchar(17) DEFAULT NULL,
  `action` varchar(63) DEFAULT NULL,
  `action_primary` varchar(63) DEFAULT NULL,
  `params` varchar(8192) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `search`
--

DROP TABLE IF EXISTS `search`;
CREATE TABLE IF NOT EXISTS `search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_version` varchar(5) DEFAULT NULL,
  `blog_name` varchar(31) DEFAULT NULL,
  `created_date` varchar(11) DEFAULT NULL,
  `created_time` varchar(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_agent` varchar(2047) DEFAULT NULL,
  `ip` varchar(17) DEFAULT NULL,
  `category_id` varchar(63) DEFAULT NULL,
  `params` varchar(8192) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
