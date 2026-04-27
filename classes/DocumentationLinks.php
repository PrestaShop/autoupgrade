<?php
/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace PrestaShop\Module\AutoUpgrade;

class DocumentationLinks
{
    public const DEV_DOC_BASE_VERSION = '8';

    public static function getDevDocUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION): string
    {
        return "https://devdocs.prestashop-project.org/{$prestashopVersion}";
    }

    public static function getDevDocUpToDateUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION): string
    {
        return self::getDevDocUrl($prestashopVersion) . '/basics/keeping-up-to-date';
    }

    public static function getDevDocUpdateAssistantUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION): string
    {
        return self::getDevDocUpToDateUrl($prestashopVersion) . '/update';
    }

    public static function getDevDocUpdateAssistantCliUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION): string
    {
        return self::getDevDocUpdateAssistantUrl($prestashopVersion) . '/update-from-the-cli';
    }

    public static function getDevDocUpdateAssistantWebUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION): string
    {
        return self::getDevDocUpdateAssistantUrl($prestashopVersion) . '/update-from-the-back-office';
    }

    public static function getDevDocUpdateAssistantPostUpdateUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION): string
    {
        return self::getDevDocUpdateAssistantUrl($prestashopVersion) . '/post-update-checklist';
    }

    public static function getDevDocUpdateAssistantPostRestoreUrl(string $prestashopVersion = self::DEV_DOC_BASE_VERSION): string
    {
        return self::getDevDocUpdateAssistantUrl($prestashopVersion) . '/post-restore-checklist';
    }

    public static function getPrestashopProjectUrl(): string
    {
        return 'https://www.prestashop-project.org';
    }

    public static function getPrestashopProjectDataTransparencyUrl(): string
    {
        return self::getPrestashopProjectUrl() . '/data-transparency';
    }

    public static function getFindSupportUrl(): string
    {
        return 'https://www.prestashop-project.org/support/';
    }

    public static function getPrestashopReleasesUrl(): string
    {
        return 'https://build.prestashop-project.org/tag/releases/';
    }

    public static function getRepositoryUrl(): string
    {
        return 'https://github.com/PrestaShop/autoupgrade';
    }

    public static function getDiscussionsAboutKnownIssuesUrl(?string $impactedVersion = null): string
    {
        $discussionsQuery = ['is:open'];
        if ($impactedVersion) {
            $discussionsQuery[] = 'label:"Impacts: ' . $impactedVersion . '"';
        }

        return self::getRepositoryUrl() . '/discussions/categories/known-issues?' . http_build_query(['discussions_q' => implode(' ', $discussionsQuery)]);
    }
}
