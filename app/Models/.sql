ALTER TABLE `spicy_shopper`.`orders`   
	DROP COLUMN `address_id`, 
	ADD COLUMN `address_id` BIGINT NULL AFTER `status`;
ALTER TABLE `spicy_shopper`.`orders`   
	CHANGE `status` `status` ENUM('pending','confirmed','shipped','delivered','cancelled','scheduled') CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending' NOT NULL;
