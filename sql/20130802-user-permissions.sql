ALTER TABLE  `user` ADD  `u_s_id` INT NULL DEFAULT NULL AFTER  `u_bg_id`;

ALTER TABLE  `user` ADD  `u_pg_id` INT NOT NULL DEFAULT  '2' AFTER  `u_pword_change`;
