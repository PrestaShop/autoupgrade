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

namespace PrestaShop\Module\AutoUpgrade;

class DocumentationLinks
{
    public const DEV_DOC_BASE_VERSION = '8';

    /** @return string */
    public static function getDevDocUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION)
    {
        return "https://devdocs.prestashop-project.org/{$prestashopVersion}";
    }

    /** @return string */
    public static function getDevDocUpToDateUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION)
    {
        return self::getDevDocUrl($prestashopVersion) . '/basics/keeping-up-to-date';
    }

    /** @return string */
    public static function getDevDocUpdateAssistantUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION)
    {
        return self::getDevDocUpToDateUrl($prestashopVersion) . '/update';
    }

    /** @return string */
    public static function getDevDocUpdateAssistantCliUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION)
    {
        return self::getDevDocUpdateAssistantUrl($prestashopVersion) . '/update-from-the-cli';
    }

    /** @return string */
    public static function getDevDocUpdateAssistantWebUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION)
    {
        return self::getDevDocUpdateAssistantUrl($prestashopVersion) . '/update-from-the-back-office';
    }

    /** @return string */
    public static function getDevDocUpdateAssistantPostUpdateUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION)
    {
        return self::getDevDocUpdateAssistantUrl($prestashopVersion) . '/post-update-checklist';
    }

    /** @return string */
    public static function getDevDocUpdateAssistantPostRestoreUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION)
    {
        return self::getDevDocUpdateAssistantUrl($prestashopVersion) . '/post-restore-checklist';
    }

    /** @return string */
    public static function getPrestashopProjectUrl()
    {
        return 'https://www.prestashop-project.org';
    }

    /** @return string */
    public static function getPrestashopProjectDataTransparencyUrl()
    {
        return self::getPrestashopProjectUrl() . '/data-transparency';
    }

    /** @return string */
    public static function getFindSupportUrl()
    {
        return 'https://www.prestashop-project.org/support/';
    }

    /** @return string */
    public static function getPrestashopReleasesUrl()
    {
        return 'https://build.prestashop-project.org/tag/releases/';
    }
}
