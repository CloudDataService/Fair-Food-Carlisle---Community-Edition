-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 15, 2012 at 12:44 PM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `foodshop`
--

--
-- Dumping data for table `buyinggroup`
--

INSERT INTO `buyinggroup` (`bg_id`, `bg_code`, `bg_name`, `bg_status`, `bg_addr_line1`, `bg_addr_line2`, `bg_addr_city`, `bg_addr_pcode`, `bg_addr_note`, `bg_deliveryday`) VALUES
(16, 'ffc242i6s', 'i6 second floor', 'Active', '6-8 Charlotte Square', 'Chinatown', 'Newcastle', 'NE1 4XD', 'Deliver to reception', 'Tuesday'),
(17, 'ffc345tom', 'Tommys Toms', 'Active', 'Toms Place', NULL, 'Carlisle', 'CA8 2NW', 'knock loudly', 'Tuesday');

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `cat_name`, `cat_parent_id`, `cat_slug`) VALUES
(1, 'Root Veg', NULL, 'root'),
(2, 'Long Root Veggie', 1, 'long_roots'),
(3, 'Crafts', NULL, 'crafts');

--
-- Dumping data for table `orderitem`
--

INSERT INTO `orderitem` (`oi_id`, `oi_b_id`, `oi_u_id`, `oi_s_id`, `oi_p_id`, `oi_qty`, `oi_price`, `oi_cost`, `oi_delivery_date`, `oi_ordered_date`, `oi_status`) VALUES
(1, NULL, 1, 1, 4, 10, 35, 30, '2012-11-27 01:00:00', '2012-11-13 16:26:19', 'Reserved'),
(2, NULL, 1, 1, 4, 10, 35, 30, '2012-12-18 01:00:00', '2012-11-13 16:26:19', 'Reserved'),
(3, NULL, 1, 1, 4, 10, 35, 30, '2012-11-27 01:00:00', '2012-11-13 16:27:48', 'Confirmed'),
(4, NULL, 1, 1, 4, 3, 12.9, 9.45, '2012-11-27 01:00:00', '2012-11-14 11:13:08', 'Reserved');

--
-- Dumping data for table `p2cat`
--

INSERT INTO `p2cat` (`p2cat_p_id`, `p2cat_cat_id`) VALUES
(1, 1),
(1, 2),
(3, 1),
(4, 1);

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`p_id`, `p_status`, `p_slug`, `p_code`, `p_s_id`, `p_name`, `p_description`, `p_pu_id`, `p_price`, `p_cost`, `p_image`) VALUES
(1, 'Active', 'carrots', 'car1', 2, 'Orange Carrots', NULL, 2, 0, 0, NULL),
(3, 'Active', 'potatoes', NULL, 2, 'Maris Piper Potatoes', 'These are Tom''s favourite kind of potatoes.					', 2, 0, 0, NULL),
(4, 'Active', 'king-ed', NULL, 1, 'King Edward Potatoes', 'Potatoes fit for a king.', 2, 4.3, 3.15, '4-47cd32934fc064aa51affa72c7183559.jpg');

--
-- Dumping data for table `productcommitment`
--

INSERT INTO `productcommitment` (`pc_id`, `pc_p_id`, `pc_min_qty`, `pc_max_qty`, `pc_period_start`, `pc_period_end`, `pc_preseason_gap`, `pc_predelivery_gap`) VALUES
(30, 4, 1, 497, '2012-11-04', '2012-11-30', 1, 1);

--
-- Dumping data for table `productunit`
--

INSERT INTO `productunit` (`pu_id`, `pu_single`, `pu_plural`, `pu_short_single`, `pu_short_plural`) VALUES
(1, 'gramme', 'grammes', 'g', 'g'),
(2, 'kilogramme', 'kilogrammes', 'kg', 'kg'),
(3, 'item', 'items', 'item', 'items'),
(4, 'box', 'boxes', 'box', 'boxes');

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`s_id`, `s_name`, `s_image`) VALUES
(1, 'West Farm', '1-7fdbcc3053e543fa8de4186057014706.jpg'),
(2, 'Craigs Cottage', NULL);

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`u_id`, `u_uname`, `u_email`, `u_pword`, `u_pword_change`, `u_bg_id`, `u_advocate`, `u_title`, `u_fname`, `u_sname`, `u_telephone`, `u_type`, `u_status`, `u_datetime_last_login`) VALUES
(1, 'gregory', 'gregory@website-lab.co.uk', '7884ac519aa535dbd434e4e18e6469c8', 0, NULL, 0, 'Mr', 'Gregor', 'Marler', '0123456789', 'Admin', 'Active', '2012-11-15 12:25:19'),
(3, 'tom', 'tom@website-lab.co.uk', '7884ac519aa535dbd434e4e18e6469c8', 0, 16, 0, 'Mr', 'Tom', 'Lloyd', NULL, 'Customer', 'Active', '2012-11-14 19:27:02');
