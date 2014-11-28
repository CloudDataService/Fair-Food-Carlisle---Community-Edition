-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 12, 2013 at 10:30 AM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `foodshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE IF NOT EXISTS `activity_log` (
  `al_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Log entry ID',
  `al_pct_id` smallint(5) unsigned NOT NULL COMMENT 'PCT ID',
  `al_u_id` smallint(5) unsigned NOT NULL COMMENT 'User ID',
  `al_type` char(15) NOT NULL COMMENT 'Type of activity',
  `al_area` varchar(32) NOT NULL COMMENT 'Application section',
  `al_uri` varchar(255) NOT NULL COMMENT 'Current URI',
  `al_description` varchar(255) NOT NULL COMMENT 'Description of entry',
  `al_datetime` datetime NOT NULL COMMENT 'Timestamp of entry',
  `al_ip` char(15) NOT NULL COMMENT 'IP Address',
  `al_ua` varchar(255) NOT NULL COMMENT 'User agent string',
  `al_browser` varchar(64) NOT NULL COMMENT 'Browser name and version',
  PRIMARY KEY (`al_id`),
  KEY `al_pct_id` (`al_pct_id`),
  KEY `al_u_id` (`al_u_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- Table structure for table `bill`
--

CREATE TABLE IF NOT EXISTS `bill` (
  `b_id` int(11) NOT NULL AUTO_INCREMENT,
  `b_u_id` int(11) DEFAULT NULL,
  `b_items` int(11) DEFAULT NULL,
  `b_price` decimal(15,2) DEFAULT NULL,
  `b_cost` decimal(15,2) DEFAULT NULL,
  `b_payment_method` enum('Go Cardless','Go Cardless Pre-Auth','Cheque','Cash','Other') DEFAULT NULL,
  `b_payment_date` datetime DEFAULT NULL,
  `b_status` enum('Draft','Pending','Paid') NOT NULL DEFAULT 'Draft',
  `b_note` longtext,
  PRIMARY KEY (`b_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=258 ;

-- --------------------------------------------------------

--
-- Table structure for table `bill_adjustment`
--

CREATE TABLE IF NOT EXISTS `bill_adjustment` (
  `ba_id` int(11) NOT NULL AUTO_INCREMENT,
  `ba_b_id` int(11) NOT NULL,
  `ba_description` text NOT NULL,
  `ba_price` decimal(15,2) NOT NULL,
  `ba_applied_date` datetime NOT NULL,
  `ba_applied_u_id` int(11) NOT NULL,
  PRIMARY KEY (`ba_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=110 ;

-- --------------------------------------------------------

--
-- Table structure for table `buyinggroup`
--

CREATE TABLE IF NOT EXISTS `buyinggroup` (
  `bg_id` int(11) NOT NULL AUTO_INCREMENT,
  `bg_code` text,
  `bg_name` text,
  `bg_status` enum('New','Active','Disabled') NOT NULL DEFAULT 'New',
  `bg_addr_line1` text,
  `bg_addr_line2` text,
  `bg_addr_city` text,
  `bg_addr_pcode` text,
  `bg_addr_note` text,
  `bg_deliveryday` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL DEFAULT 'Tuesday',
  PRIMARY KEY (`bg_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_page_order` int(3) NOT NULL DEFAULT '10',
  `cat_name` text NOT NULL,
  `cat_parent_id` int(11) DEFAULT NULL,
  `cat_slug` text NOT NULL,
  `cat_image` varchar(255) NOT NULL,
  `cat_description` longtext,
  `cat_status` enum('Active','Hidden','Removed') NOT NULL,
  `cat_show_products` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `emails_queue`
--

CREATE TABLE IF NOT EXISTS `emails_queue` (
  `eq_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `eq_email` varchar(255) NOT NULL,
  `eq_subject` varchar(255) NOT NULL,
  `eq_body` text NOT NULL,
  `eq_attachment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`eq_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `orderitem`
--

CREATE TABLE IF NOT EXISTS `orderitem` (
  `oi_id` int(11) NOT NULL AUTO_INCREMENT,
  `oi_b_id` int(11) DEFAULT NULL,
  `oi_u_id` int(11) NOT NULL,
  `oi_s_id` int(11) NOT NULL,
  `oi_p_id` int(11) NOT NULL,
  `oi_qty` int(11) NOT NULL,
  `oi_price` decimal(15,2) NOT NULL,
  `oi_cost` decimal(15,2) NOT NULL,
  `oi_delivery_date` date NOT NULL,
  `oi_ordered_date` datetime NOT NULL,
  `oi_status` enum('Reserved','Confirmed','Expired','Cancelled','Rejected','Unavailable') NOT NULL,
  PRIMARY KEY (`oi_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1358 ;

-- --------------------------------------------------------

--
-- Table structure for table `order_recurring`
--

CREATE TABLE IF NOT EXISTS `order_recurring` (
  `or_id` int(11) NOT NULL AUTO_INCREMENT,
  `or_u_id` int(11) NOT NULL,
  `or_s_id` int(11) NOT NULL,
  `or_p_id` int(11) NOT NULL,
  `or_qty` int(11) NOT NULL,
  `or_frequency` enum('weekly','fortnightly') NOT NULL,
  `or_status` enum('Pending','Confirmed','Cancelled','Stopped','Finished') NOT NULL,
  `or_started_date` date NOT NULL,
  `or_latest_date` date DEFAULT NULL,
  `or_finished_date` datetime DEFAULT NULL,
  PRIMARY KEY (`or_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=17 ;

-- --------------------------------------------------------

--
-- Table structure for table `p2cat`
--

CREATE TABLE IF NOT EXISTS `p2cat` (
  `p2cat_p_id` int(11) NOT NULL,
  `p2cat_cat_id` int(11) NOT NULL,
  PRIMARY KEY (`p2cat_p_id`,`p2cat_cat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `p2pc`
--

CREATE TABLE IF NOT EXISTS `p2pc` (
  `p2pc_id` int(11) NOT NULL AUTO_INCREMENT,
  `p2pc_p_id` int(11) NOT NULL,
  `p2pc_pc_id` int(11) NOT NULL,
  `p2pc_stock` int(11) NOT NULL,
  PRIMARY KEY (`p2pc_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Links products to named seasons.' AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `p_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_page_order` int(3) NOT NULL DEFAULT '10',
  `p_status` enum('Active','Draft','Removed') NOT NULL DEFAULT 'Draft',
  `p_slug` text NOT NULL,
  `p_code` text,
  `p_s_id` int(11) NOT NULL,
  `p_name` text NOT NULL,
  `p_description` text,
  `p_pu_id` int(11) DEFAULT NULL,
  `p_price` decimal(15,2) NOT NULL,
  `p_cost` decimal(15,2) NOT NULL,
  `p_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`p_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=44 ;

-- --------------------------------------------------------

--
-- Table structure for table `productcommitment`
--

CREATE TABLE IF NOT EXISTS `productcommitment` (
  `pc_id` int(11) NOT NULL AUTO_INCREMENT,
  `pc_name` varchar(255) DEFAULT NULL,
  `pc_p_id` int(11) NOT NULL,
  `pc_min_qty` int(11) NOT NULL,
  `pc_max_qty` int(11) NOT NULL,
  `pc_period_start` date NOT NULL,
  `pc_period_end` date NOT NULL,
  `pc_preseason_gap` int(11) NOT NULL,
  `pc_predelivery_gap` int(11) NOT NULL,
  PRIMARY KEY (`pc_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=275 ;

-- --------------------------------------------------------

--
-- Table structure for table `productunit`
--

CREATE TABLE IF NOT EXISTS `productunit` (
  `pu_id` int(11) NOT NULL AUTO_INCREMENT,
  `pu_single` text NOT NULL,
  `pu_plural` text NOT NULL,
  `pu_short_single` text,
  `pu_short_plural` text,
  PRIMARY KEY (`pu_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `productunit`
--

INSERT INTO `productunit` (`pu_id`, `pu_single`, `pu_plural`, `pu_short_single`, `pu_short_plural`) VALUES
(13, 'gramme', 'grammes', 'g', 'g'),
(14, 'item', 'items', 'item', 'items'),
(15, 'pack', 'packs', 'pack', 'packs'),
(16, '500g bag', '500g bags', 'x 500g', 'x 500g');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE IF NOT EXISTS `supplier` (
  `s_id` int(11) NOT NULL AUTO_INCREMENT,
  `s_name` text NOT NULL,
  `s_image` varchar(255) DEFAULT NULL,
  `s_description` text,
  PRIMARY KEY (`s_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `u_id` int(11) NOT NULL AUTO_INCREMENT,
  `u_uname` text NOT NULL,
  `u_email` text NOT NULL,
  `u_pword` text NOT NULL,
  `u_pword_change` tinyint(1) NOT NULL DEFAULT '0',
  `u_bg_id` int(11) DEFAULT NULL,
  `u_advocate` tinyint(1) NOT NULL DEFAULT '0',
  `u_title` text,
  `u_fname` text NOT NULL,
  `u_sname` text NOT NULL,
  `u_telephone` text,
  `u_type` enum('Admin','Member') NOT NULL,
  `u_status` enum('Pending','Active','Removed') NOT NULL,
  `u_datetime_last_login` datetime DEFAULT NULL,
  `u_gc_preauth_id` text,
  PRIMARY KEY (`u_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=90 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


--
-- Data for initial user
--

INSERT INTO `user` (`u_id`, `u_uname`, `u_email`, `u_pword`, `u_pword_change`, `u_bg_id`, `u_advocate`, `u_title`, `u_fname`, `u_sname`, `u_telephone`, `u_type`, `u_status`, `u_datetime_last_login`, `u_gc_preauth_id`) VALUES
(1, 'gregory', 'gregory@website-lab.co.uk', '7884ac519aa535dbd434e4e18e6469c8', 0, 16, 0, 'Mr', 'Gregory', 'Marler', '0123456789', 'Admin', 'Active', '2013-07-11 09:22:59', NULL);
