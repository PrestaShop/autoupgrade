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

import RenderBackupSelection from "../../../views/templates/components/render-backup-selection.html.twig";

export default {
  title: "Components/Render fields",
  component: RenderBackupSelection,
};

export const BackupSelection = {
  args: {
    backup_selection: true,
    form_fields: {
      BACKUP_NAME: "BACKUP_NAME",
    },
    backups_available: [
      {
        filename: "V1.7.6.3_20250118-120000-7f540970",
        datetime: "01/18/25 12:00:00",
      },
      {
        filename: "V1.7.6.3_20250122-180000-8D420630",
        datetime: "01/22/25 18:00:00",
      },
    ],
    current_backup: "V1.7.6.3_20250118-120000-7f540970",
    error_message: "",
  },
};
