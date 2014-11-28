
--
-- Table structure for table `order_note`
--

CREATE TABLE IF NOT EXISTS `order_note` (
  `on_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `on_u_id` int(10) unsigned NOT NULL,
  `on_delivery_date` datetime NOT NULL,
  `on_text` text NOT NULL,
  `on_added_by` int(10) unsigned NOT NULL,
  `on_added_on` datetime NOT NULL,
  PRIMARY KEY (`on_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

