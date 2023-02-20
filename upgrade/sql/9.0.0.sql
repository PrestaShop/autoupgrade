SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

ALTER TABLE `PREFIX_orders` ADD INDEX `invoice_date` (`invoice_date`);
