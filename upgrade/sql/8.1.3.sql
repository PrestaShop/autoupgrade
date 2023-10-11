SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

ALTER TABLE co_customer 
	CHANGE `ape` `ape` varchar(6) DEFAULT NULL;
