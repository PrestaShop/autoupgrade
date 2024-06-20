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

namespace PrestaShop\Module\AutoUpgrade\Services;

use PrestaShop\Module\AutoUpgrade\Exceptions\DistributionApiException;

class DistributionApiService
{
    public const API_URL = 'https://api.prestashop-project.org';

    /**
     * @throws DistributionApiException
     *
     * @return array<string, string>
     */
    public function getPhpVersionRequirements(string $targetVersion): array
    {
        $response = @file_get_contents(self::API_URL . '/prestashop');

        if (!$response) {
            throw new DistributionApiException('Error when retrieving Prestashop versions from Distribution api', DistributionApiException::API_NOT_CALLABLE_CODE);
        } else {
            $data = json_decode($response, true);
            foreach ($data as $versionInfo) {
                if ($versionInfo['version'] === $targetVersion) {
                    return [
                        'php_min_version' => $versionInfo['php_min_version'],
                        'php_max_version' => $versionInfo['php_max_version'],
                    ];
                }
            }
        }
        throw new DistributionApiException('No version match in Distribution api for ' . $targetVersion, DistributionApiException::VERSION_NOT_FOUND_CODE);
    }
}
