
-- --------------------------------------------------------

--
-- Table structure for table `permission_group`
--

CREATE TABLE IF NOT EXISTS `permission_group` (
  `pg_id` int(11) NOT NULL AUTO_INCREMENT,
  `pg_name` varchar(100) NOT NULL,
  PRIMARY KEY (`pg_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `permission_group`
--

INSERT INTO `permission_group` (`pg_id`, `pg_name`) VALUES
(1, 'Admin (top level)'),
(2, 'Depot Staff/Volunteer'),
(3, 'Buying Group Managers'),
(4, 'Supplier');

--
-- Table structure for table `permission_item`
--

CREATE TABLE IF NOT EXISTS `permission_item` (
  `pi_id` int(11) NOT NULL AUTO_INCREMENT,
  `pi_name` varchar(100) NOT NULL,
  `pi_key` varchar(100) NOT NULL,
  `pi_default` tinyint(1) NOT NULL,
  PRIMARY KEY (`pi_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `permission_item`
--

INSERT INTO `permission_item` (`pi_id`, `pi_name`, `pi_key`, `pi_default`) VALUES
(1, 'Access and edit all areas (grants all permissions)', 'do_anything', 0),
(2, 'View and manage member bills', 'view_member_bills', 0),
(3, 'View picking lists', 'view_picking_lists', 0),
(4, 'View buying group details', 'view_buying_groups', 0),
(5, 'View members and staff', 'view_members', 0),
(6, 'View all reports', 'view_reports', 0),
(7, 'View and edit produce', 'manage_products', 0),
(8, 'View and edit categories', 'manage_categories', 0),
(9, 'View and edit producer details', 'manage_suppliers', 0),
(10, 'View and edit seasons', 'manage_seasons', 0),
(11, 'Edit permissions of members', 'manage_member_permissions', 0),
(12, 'View and edit members and staff', 'manage_members', 0);


--
-- Table structure for table `pg2pi`
--

CREATE TABLE IF NOT EXISTS `pg2pi` (
  `pg2pi_pg_id` int(11) NOT NULL,
  `pg2pi_pi_id` int(11) NOT NULL,
  `pg2pi_value` tinyint(1) NOT NULL,
  `pg2pi_bg_value` int(11) NOT NULL,
  `pg2pi_s_value` int(11) NOT NULL,
  UNIQUE KEY `pg2pi_pg_id` (`pg2pi_pg_id`,`pg2pi_pi_id`),
  KEY `pg2pi_pg_id_2` (`pg2pi_pg_id`,`pg2pi_pi_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pg2pi`
--

INSERT INTO `pg2pi` (`pg2pi_pg_id`, `pg2pi_pi_id`, `pg2pi_value`, `pg2pi_bg_value`, `pg2pi_s_value`) VALUES
(1, 1, 1, 0, 0),
(1, 11, 1, 0, 0),
(2, 2, 1, 0, 0),
(2, 3, 1, 0, 0),
(2, 4, 1, 0, 0),
(2, 5, 1, 0, 0),
(2, 7, 1, 0, 0),
(2, 8, 1, 0, 0),
(2, 9, 1, 0, 0),
(2, 10, 1, 0, 0),
(2, 12, 1, 0, 0),
(3, 2, 0, 1, 0),
(3, 3, 0, 1, 0),
(3, 5, 0, 1, 0),
(3, 6, 0, 1, 0),
(4, 3, 0, 0, 1),
(4, 6, 0, 0, 1),
(4, 7, 0, 0, 1),
(4, 9, 0, 0, 1);
