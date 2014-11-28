ALTER TABLE `order_recurring` CHANGE `or_frequency` `or_frequency` ENUM('weekly','fortnightly','monthly') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;
