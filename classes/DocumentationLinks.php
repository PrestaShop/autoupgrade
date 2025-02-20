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
    public const DEV_DOC_URL = 'https://devdocs.prestashop-project.org/8';
    public const DEV_DOC_UP_TO_DATE_URL = self::DEV_DOC_URL . '/basics/keeping-up-to-date';
    public const DEV_DOC_UPGRADE_URL = self::DEV_DOC_UP_TO_DATE_URL . '/upgrade-module';
    public const DEV_DOC_UPGRADE_CLI_URL = self::DEV_DOC_UPGRADE_URL . '/upgrade-cli';
    public const DEV_DOC_UPGRADE_WEB_URL = self::DEV_DOC_UP_TO_DATE_URL . '/use-autoupgrade-module';
    public const DEV_DOC_UPGRADE_POST_UPGRADE_URL = self:: DEV_DOC_UPGRADE_URL . '/post-update-checklist';
    public const DEV_DOC_UPGRADE_POST_RESTORE_URL = self:: DEV_DOC_UPGRADE_URL . '/post-restore-checklist';
    public const PRESTASHOP_PROJECT_URL = 'https://www.prestashop-project.org';
    public const PRESTASHOP_PROJECT_DATA_TRANSPARENCY_URL = self::PRESTASHOP_PROJECT_URL . '/data-transparency';
    public const PRESTASHOP_EXPERTS = 'https://experts.prestashop.com/english/experts/';
}
