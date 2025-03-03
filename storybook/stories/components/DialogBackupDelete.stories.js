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

import DialogBackupDelete from "../../../views/templates/dialogs/dialog-delete-backup.html.twig";

export default {
  title: "Components/Dialog",
  component: DialogBackupDelete,
};

export const BackupDelete = {
  args: {
    backup_name: "V1.7.6.3_20250118-120000-7f540970",
    backup_date: "01/18/25 12:00:00",
    only_backup: true,
    form_name: "delete-backup",
    form_route_to_confirm_delete: "/",
    form_fields: {
      BACKUP_NAME: "backup_name",
    },
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
