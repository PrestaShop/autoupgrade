/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
      backup_name: "backup_name",
    },
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
