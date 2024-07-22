<?php

namespace PrestaShop\Module\AutoUpgrade\UpgradeTools\Module;

class ModuleVersionAdapter
{
    static function get(string $name): ?string
    {
        return \Db::getInstance()->getValue(
            'SELECT version FROM `' . _DB_PREFIX_ . 'module` WHERE name = "' . $name . '"'
        );
    }

    static function update(string $name, string $version): boolean
    {
        return \Db::getInstance()->execute('
            UPDATE `' . _DB_PREFIX_ . 'module` m
            SET m.version = \'' . pSQL($version) . '\'
            WHERE m.name = \'' . pSQL($name) . '\'');
    }
}
