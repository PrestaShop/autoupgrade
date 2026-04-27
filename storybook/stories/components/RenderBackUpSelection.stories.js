/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import RenderBackupSelection from "../../../views/templates/components/render-backup-selection.html.twig";

export default {
  title: "Components/Render fields",
  component: RenderBackupSelection,
  argTypes: {
    backup_selection: { control: false },
  },
};

export const BackupSelection = {
  args: {
    backup_selection: true,
    form_fields: {
      backup_name: "backup_name",
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
