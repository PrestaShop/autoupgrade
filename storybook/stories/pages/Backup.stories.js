/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import BackupPage from "../../../views/templates/pages/update.html.twig";
import { Backup as Stepper } from "../components/Stepper.stories";

export default {
  component: BackupPage,
  id: "33",
  title: "Pages/Update",
};

export const Backup = {
  args: {
    // Step
    step: {
      code: "backup",
      title: "Backup",
    },
    backup_completed: false,
    download_path: "#",
    filename: "backup.zip",
    form_fields: {
      include_images: {
        field: "keep_images",
        value: true,
      },
    },
    form_route_to_save: "update-step-backup-save-option",
    form_route_to_submit: "update-step-backup-submit-backup",
    form_route_to_confirm_update: "update-step-backup-confirm-update",
    form_route_to_confirm_backup: "update-step-backup-confirm-backup",
    form_route_to_submit_update: "update-step-backup-submit-update",
    form_route_to_submit_backup: "update-step-update-options-submit-form",
    step_parent_id: "ua_container",
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    // Stepper
    ...Stepper.args,
  },
};
