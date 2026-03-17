<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters;

use PrestaShop\Module\AutoUpgrade\UpgradeTools\Translator;

class ConfigurationValidator
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<array{'message': string, 'target': string}>
     */
    public function validate(array $array = []): array
    {
        $errors = [];

        $isLocal = isset($array[UpgradeConfiguration::CHANNEL]) && $array[UpgradeConfiguration::CHANNEL] === UpgradeConfiguration::CHANNEL_LOCAL;

        foreach ($array as $key => $value) {
            switch ($key) {
                case UpgradeConfiguration::CHANNEL:
                    $error = $this->validateChannel($value);
                    break;
                case UpgradeConfiguration::ARCHIVE_ZIP:
                    $error = $this->validateArchiveZip($value, $isLocal);
                    break;
                case UpgradeConfiguration::ARCHIVE_XML:
                    $error = $this->validateArchiveXml($value, $isLocal);
                    break;
                case UpgradeConfiguration::PS_AUTOUP_CUSTOM_MOD_DESACT:
                case UpgradeConfiguration::PS_AUTOUP_REGEN_EMAIL:
                case UpgradeConfiguration::PS_AUTOUP_KEEP_IMAGES:
                case UpgradeConfiguration::PS_DISABLE_OVERRIDES:
                    $error = $this->validateBool($value, $key);
                    break;
            }

            if (isset($error)) {
                $errors[] = ['message' => $error, 'target' => $key];
            }
        }

        $channelLocalError = $this->validateArchivesOnlyForLocalChannel($array, $isLocal);
        if ($channelLocalError !== null) {
            $errors[] = $channelLocalError;
        }

        return $errors;
    }

    private function validateChannel(string $channel): ?string
    {
        if (!in_array($channel, [
            UpgradeConfiguration::CHANNEL_LOCAL,
            UpgradeConfiguration::CHANNEL_ONLINE,
            UpgradeConfiguration::CHANNEL_ONLINE_RECOMMENDED,
        ], true)) {
            return $this->translator->trans('Unknown channel %s', [$channel]);
        }

        return null;
    }

    private function validateArchiveZip(string $zip, bool $isLocal): ?string
    {
        if ($isLocal && empty($zip)) {
            return $this->translator->trans('No zip archive provided');
        }

        return null;
    }

    private function validateArchiveXml(string $xml, bool $isLocal): ?string
    {
        if ($isLocal && empty($xml)) {
            return $this->translator->trans('No xml archive provided');
        }

        return null;
    }

    /**
     * @param string|bool $boolValue
     */
    private function validateBool($boolValue, string $key): ?string
    {
        if (!is_bool($boolValue) && ($boolValue === '' || filter_var($boolValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === null)) {
            return $this->translator->trans('Value must be a boolean for %s', [$key]);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array{'message': string, 'target': string}|null
     */
    private function validateArchivesOnlyForLocalChannel(array $array, bool $isLocal): ?array
    {
        // We didn't want to return an error if the customer hadn't entered a channel.
        if (!isset($array[UpgradeConfiguration::CHANNEL]) || $isLocal) {
            return null;
        }

        if (!empty($array[UpgradeConfiguration::ARCHIVE_ZIP] ?? null) || !empty($array[UpgradeConfiguration::ARCHIVE_XML] ?? null)) {
            return [
                'message' => $this->translator->trans('Zip or XML archives are only allowed with the local channel'),
                'target' => UpgradeConfiguration::CHANNEL,
            ];
        }

        return null;
    }
}
