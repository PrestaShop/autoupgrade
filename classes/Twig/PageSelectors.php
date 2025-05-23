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

namespace PrestaShop\Module\AutoUpgrade\Twig;

class PageSelectors
{
    public const APP_PARENT_ID = 'update_assistant';
    public const PAGE_PARENT_ID = 'ua_page';
    public const STEP_PARENT_ID = 'ua_container';
    public const STEPPER_PARENT_ID = 'stepper_content';
    public const DIALOG_PARENT_ID = 'ua_dialog';
    public const RADIO_CARD_ONLINE_PARENT_ID = 'radio_card_online';
    public const RADIO_CARD_ARCHIVE_PARENT_ID = 'radio_card_archive';
    public const DOWNLOAD_LOGS_PARENT_ID = 'download_logs';
    public const NOTIFICATION_PARENT_ID = 'update_assistant_notification';
    public const TEMPERED_FILES_CONTAINER_ID = 'tempered_files_container';

    /**
     * @return array<string, string>
     */
    public static function getAllSelectors(): array
    {
        return [
            'app_parent_id' => self::APP_PARENT_ID,
            'page_parent_id' => self::PAGE_PARENT_ID,
            'step_parent_id' => self::STEP_PARENT_ID,
            'stepper_parent_id' => self::STEPPER_PARENT_ID,
            'dialog_parent_id' => self::DIALOG_PARENT_ID,
            'radio_card_online_parent_id' => self::RADIO_CARD_ONLINE_PARENT_ID,
            'radio_card_archive_parent_id' => self::RADIO_CARD_ARCHIVE_PARENT_ID,
            'download_logs_parent_id' => self::DOWNLOAD_LOGS_PARENT_ID,
        ];
    }
}
