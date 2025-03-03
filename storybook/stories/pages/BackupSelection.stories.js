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

import BackupSelectionPage from "../../../views/templates/pages/restore.html.twig";
import { BackupSelection as BackupSelectionComponent } from "../components/RenderBackUpSelection.stories";
import { BackupSelection as Stepper } from "../components/Stepper.stories";

export default {
  component: BackupSelectionPage,
  id: "40",
  title: "Pages/Rollback",
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
