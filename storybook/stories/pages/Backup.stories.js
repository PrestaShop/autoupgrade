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
        field: "PS_AUTOUP_KEEP_IMAGES",
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
