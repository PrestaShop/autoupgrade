SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* We forgot to update redirect_type in 8.1.0. This updates it, if it's not done already */
/* PHP:ps_811_update_redirect_type(); */;

ALTER TABLE `PREFIX_authorized_application` CHANGE `name` `name` VARCHAR(50) NOT NULL;
