<?php

namespace PrestaShop\Module\AutoUpgrade\Database;

class TableFilter
{
    /**
     * @return string[]
     */
    public static function tablesToIgnore(): array {
        return [
            _DB_PREFIX_ . 'connections',
            _DB_PREFIX_ . 'connections_page',
            _DB_PREFIX_ . 'connections_source',
            _DB_PREFIX_ . 'guest',
            _DB_PREFIX_ . 'statssearch',
        ];
    }
}
