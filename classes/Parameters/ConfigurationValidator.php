<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use PrestaShop\Module\AutoUpgrade\Upgrader;
use UnexpectedValueException;

class ConfigurationValidator
{
    /**
     * @param array<string, mixed> $array
     *
     * @return void
     *
     * @throws UnexpectedValueException
     */
    public function validate(array $array = []): void
    {
        $isLocal = isset($array['channel']) && $array['channel'] === Upgrader::CHANNEL_LOCAL;

        foreach ($array as $key => $value) {
            switch ($key) {
                case 'channel':
                    $this->validateChannel($value);
                    break;
                case 'archive_zip':
                    $this->validateArchiveZip($value, $isLocal);
                    break;
                case 'archive_xml':
                    $this->validateArchiveXml($value, $isLocal);
                    break;
                case 'PS_AUTOUP_CUSTOM_MOD_DESACT':
                case 'PS_AUTOUP_KEEP_MAILS':
                case 'PS_AUTOUP_KEEP_IMAGES':
                case 'PS_DISABLE_OVERRIDES':
                    $this->validateBool($value, $key);
                    break;
            }
        }
    }

    /**
     * @throws UnexpectedValueException
     */
    private function validateChannel(string $channel): void
    {
        if ($channel !== Upgrader::CHANNEL_LOCAL && $channel !== Upgrader::CHANNEL_ONLINE) {
            throw new UnexpectedValueException('Unknown channel ' . $channel);
        }
    }

    /**
     * @throws UnexpectedValueException
     */
    private function validateArchiveZip(string $zip, bool $isLocal): void
    {
        if ($isLocal && empty($zip)) {
            throw new UnexpectedValueException('No zip archive provided');
        }
    }

    /**
     * @throws UnexpectedValueException
     */
    private function validateArchiveXml(string $xml, bool $isLocal): void
    {
        if ($isLocal && empty($xml)) {
            throw new UnexpectedValueException('No xml archive provided');
        }
    }

    /**
     * @throws UnexpectedValueException
     */
    private function validateBool(string $boolValue, string $key): void
    {
        if (filter_var($boolValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null) {
            throw new UnexpectedValueException('Value must be a boolean for ' . $key);
        }
    }
}
