-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 08, 2014 at 06:17 PM
-- Server version: 5.5.35
-- PHP Version: 5.4.4-14+deb7u8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `donations`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE IF NOT EXISTS `cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `steamid` varchar(30) DEFAULT NULL,
  `avatar` varchar(256) DEFAULT NULL,
  `avatarmedium` varchar(256) DEFAULT NULL,
  `avatarfull` varchar(256) DEFAULT NULL,
  `personaname` varchar(128) DEFAULT NULL,
  `timestamp` varchar(32) DEFAULT NULL,
  `steamid64` varchar(64) DEFAULT NULL,
  `steam_link` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `custom_chatcolors`
--

CREATE TABLE IF NOT EXISTS `custom_chatcolors` (
  `index` int(11) NOT NULL AUTO_INCREMENT,
  `identity` varchar(32) CHARACTER SET latin1 NOT NULL,
  `flag` char(1) CHARACTER SET latin1 DEFAULT NULL,
  `tag` varchar(32) CHARACTER SET latin1 DEFAULT NULL,
  `tagcolor` varchar(8) CHARACTER SET latin1 DEFAULT NULL,
  `namecolor` varchar(8) CHARACTER SET latin1 DEFAULT NULL,
  `textcolor` varchar(8) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`index`),
  UNIQUE KEY `identity` (`identity`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8  ;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE IF NOT EXISTS `donations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `steamId` varchar(24) NOT NULL,
  `itemId` int(10) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `steamId` (`steamId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE IF NOT EXISTS `donors` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `steam_id` varchar(30) NOT NULL,
  `sign_up_date` varchar(10) NOT NULL,
  `email` varchar(60) DEFAULT NULL,
  `renewal_date` varchar(10) DEFAULT '0',
  `current_amount` varchar(10) NOT NULL,
  `total_amount` float DEFAULT NULL,
  `expiration_date` varchar(10) NOT NULL,
  `steam_link` varchar(200) DEFAULT NULL,
  `notes` varchar(200) DEFAULT NULL,
  `activated` varchar(1) DEFAULT '0',
  `txn_id` varchar(128) DEFAULT NULL,
  `tier` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `group_id` varchar(8) NOT NULL,
  `srv_group_id` varchar(8) NOT NULL,
  `server_id` varchar(8) NOT NULL,
  `multiplier` varchar(8) NOT NULL,
  `ccc_enabled` int(1) NOT NULL DEFAULT '0',
  `minimum` int(8) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
