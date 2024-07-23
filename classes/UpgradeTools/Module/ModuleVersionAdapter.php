<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

class ModuleVersionAdapter
{
    public static function get(string $name): ?string
    {
        return \Db::getInstance()->getValue(
            'SELECT version FROM `' . _DB_PREFIX_ . 'module` WHERE name = "' . $name . '"'
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
