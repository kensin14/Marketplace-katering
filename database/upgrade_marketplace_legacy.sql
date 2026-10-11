-- Run once after importing marketplace_katering.sql into marketplace_katering.
-- Existing member accounts become merchants by default. Update selected accounts
-- to role='customer' in phpMyAdmin if they represent offices placing orders.

ALTER TABLE `login` MODIFY `pass` VARCHAR(255) NOT NULL;
ALTER TABLE `member` ADD `role` VARCHAR(20) NOT NULL DEFAULT 'merchant';
ALTER TABLE `barang` ADD `id_member` INT(11) NULL AFTER `id`;
ALTER TABLE `barang` ADD `is_catering_menu` TINYINT(1) NOT NULL DEFAULT 0 AFTER `id_member`;
CREATE INDEX `barang_id_member_index` ON `barang` (`id_member`);
CREATE INDEX `barang_is_catering_menu_index` ON `barang` (`is_catering_menu`);

-- Existing menu and stock data belong to the administrator's merchant account.
UPDATE `barang`
SET `id_member` = (SELECT `id_member` FROM `login` WHERE `user` = 'admin' LIMIT 1)
WHERE `id_member` IS NULL;


CREATE TABLE `orders` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_number` VARCHAR(40) NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `merchant_id` INT UNSIGNED NOT NULL,
    `delivery_date` DATE NOT NULL,
    `delivery_address` VARCHAR(1000) NOT NULL,
    `notes` TEXT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `subtotal` DECIMAL(12,2) NOT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `orders_invoice_number_unique` (`invoice_number`),
    KEY `orders_customer_id_index` (`customer_id`),
    KEY `orders_merchant_id_index` (`merchant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `unit_price` DECIMAL(12,2) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL,
    `line_total` DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `order_items_order_id_index` (`order_id`),
    KEY `order_items_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
