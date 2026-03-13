/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import BackupSelectionPage from "../../../views/templates/pages/restore.html.twig";
import { BackupSelection as BackupSelectionComponent } from "../components/RenderBackUpSelection.stories";
import { BackupSelection as Stepper } from "../components/Stepper.stories";

export default {
  component: BackupSelectionPage,
  id: "40",
  title: "Pages/Restore",
};

export const BackupSelection = {
  args: {
    // Step
    step: {
      code: "backup-selection",
      title: "Backup selection",
    },
    step_parent_id: "ua_container",
    form_backup_selection_name: "backup_choice",
    form_route_to_save: "restore-step-backup-selection-save-form",
    form_route_to_submit_restore:
      "restore-step-backup-selection-submit-restore-form",
    form_route_to_submit_delete:
      "restore-step-backup-selection-submit-delete-form",
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    // Backup
    ...BackupSelectionComponent.args,
    // Stepper
    ...Stepper.args,
  },
};
