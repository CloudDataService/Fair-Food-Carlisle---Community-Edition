ALTER TABLE `orderitem` CHANGE `oi_source_or` `oi_source_id` INT(11) NULL DEFAULT NULL;

ALTER TABLE `orderitem` ADD `oi_source_type` TEXT NULL AFTER `oi_source_id`;

