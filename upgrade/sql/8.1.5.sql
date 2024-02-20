SET SESSION sql_mode='';
SET NAMES 'utf8mb4';

/* Adds missing hook entries that have been added to 8.1.5 installer. */
INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`) VALUES
  (NULL, 'actionSubmitAccountBefore', 'Triggers before customer registers', 'Triggers after submitting registration form, before the registration process itself. Allows to modify result of this action.', '1'),
  (NULL, 'actionAuthenticationBefore', 'Triggers before customer logs in', 'Triggers after successful validation of login form, before the login process itself.', '1'),
  (NULL, 'actionCartUpdateQuantityBefore', 'Triggers before product is added to cart', 'Allows responding to add to cart events.', '1'),
  (NULL, 'actionAjaxDieBefore', 'Triggers when returning AJAX response', 'Allows to modify AJAX response of controllers using ajaxRender method.', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);
  
