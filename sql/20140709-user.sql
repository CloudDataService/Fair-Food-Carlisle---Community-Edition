ALTER TABLE `user` ADD `u_delivery_type` ENUM('centre_pickup','home_delivery','selected_group') DEFAULT 'selected_group' AFTER `u_telephone`;


ALTER TABLE `user` ADD `u_addr_line1` TEXT NULL DEFAULT NULL AFTER `u_delivery_type`, ADD `u_addr_line2` TEXT NULL DEFAULT NULL AFTER `u_addr_line1`, ADD `u_addr_city` TEXT NULL DEFAULT NULL AFTER `u_addr_line2`, ADD `u_addr_pcode` TEXT NULL DEFAULT NULL AFTER `u_addr_city`;

