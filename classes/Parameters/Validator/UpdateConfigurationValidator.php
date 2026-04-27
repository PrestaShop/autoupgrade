<?php

/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade\Parameters\Validator;

use PrestaShop\Module\AutoUpgrade\Parameters\UpdateConfiguration;

class UpdateConfigurationValidator extends AbstractConfigurationValidator
{
    public function validate(array $array = []): array
    {
        $errors = [];

        $isLocal = isset($array[UpdateConfiguration::CHANNEL]) && $array[UpdateConfiguration::CHANNEL] === UpdateConfiguration::CHANNEL_LOCAL;

        foreach ($array as $key => $value) {
            switch ($key) {
                case UpdateConfiguration::CHANNEL:
                    $error = $this->validateChannel($value);
                    break;
                case UpdateConfiguration::ARCHIVE_ZIP:
                    $error = $this->validateArchiveZip($value, $isLocal);
                    break;
                case UpdateConfiguration::ARCHIVE_XML:
                    $error = $this->validateArchiveXml($value, $isLocal);
                    break;
                case UpdateConfiguration::DISABLE_NON_NATIVE_MODULES:
                case UpdateConfiguration::REGENERATE_EMAIL_TEMPLATES:
                case UpdateConfiguration::DISABLE_OVERRIDES:
                    $error = $this->validateBool($value, $key);
                    break;
                case UpdateConfiguration::MAX_FILES_PER_BATCH:
                    $error = $this->validateInt($value, $key);
                    break;
                default:
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
            UpdateConfiguration::CHANNEL_LOCAL,
            UpdateConfiguration::CHANNEL_ONLINE,
            UpdateConfiguration::CHANNEL_ONLINE_RECOMMENDED,
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
     * @param array<string, mixed> $array
     *
     * @return array{'message': string, 'target': string}|null
     */
    private function validateArchivesOnlyForLocalChannel(array $array, bool $isLocal): ?array
    {
        // We didn't want to return an error if the customer hadn't entered a channel.
        if (!isset($array[UpdateConfiguration::CHANNEL]) || $isLocal) {
            return null;
        }

        if (!empty($array[UpdateConfiguration::ARCHIVE_ZIP] ?? null) || !empty($array[UpdateConfiguration::ARCHIVE_XML] ?? null)) {
            return [
                'message' => $this->translator->trans('Zip or XML archives are only allowed with the local channel'),
                'target' => UpdateConfiguration::CHANNEL,
            ];
        }

        return null;
    }
}
