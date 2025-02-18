<?php

namespace PrestaShop\Module\AutoUpgrade\Services;

use Configuration;
use PrestaShop\Module\AutoUpgrade\Models\UpdateNotificationConfiguration;

class UpdateNotificationService
{
    const CONFIG_KEY = 'PS_AUTOUPGRADE_LAST_CHECK';

    public function getUpdateNotificationConfiguration(): UpdateNotificationConfiguration
    {
        return new UpdateNotificationConfiguration(json_decode(Configuration::get(self::CONFIG_KEY), true));
    }

    public function setUpdateNotificationConfiguration(UpdateNotificationConfiguration $configuration): void
    {
        Configuration::updateValue(self::CONFIG_KEY, $configuration->toJson());
    }
}
