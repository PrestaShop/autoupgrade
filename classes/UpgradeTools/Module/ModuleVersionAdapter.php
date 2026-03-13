<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

class ModuleVersionAdapter
{
    public static function get(string $name): ?string
    {
        return \Db::getInstance()->getValue(
            'SELECT version FROM `' . _DB_PREFIX_ . 'module` WHERE name = "' . pSQL($name) . '"'
        );
    }

    public static function update(string $name, string $version): bool
    {
        return \Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'module` m
            SET m.version = \'' . pSQL($version) . '\'
            WHERE m.name = \'' . pSQL($name) . '\'');
    }
}
