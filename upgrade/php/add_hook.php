<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

use PrestaShop\Module\AutoUpgrade\Database\DbWrapper;

/**
 * @return bool
 *
 * @throws \PrestaShop\Module\AutoUpgrade\Exceptions\UpdateDatabaseException
 */
function add_hook($hook, $title, $description, $position = 1)
{
    return (bool) DbWrapper::execute('
        INSERT INTO `' . _DB_PREFIX_ . 'hook` (`name`, `title`, `description`, `position`)
        VALUES ("' . pSQL($hook) . '", "' . pSQL($title) . '", "' . pSQL($description) . '", ' . (int) $position . ')
        ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`)    
    ');
}
