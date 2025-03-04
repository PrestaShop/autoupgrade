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

import UpdateOptionsPage from "../../../views/templates/pages/update.html.twig";
import { UpdateOptions as Stepper } from "../components/Stepper.stories";

export default {
  component: UpdateOptionsPage,
  id: "32",
  title: "Pages/Update",
};

export const UpdateOptions = {
  args: {
    // Step
    step: {
      code: "update-options",
      title: "Update options",
    },
    form_fields: {
      deactive_non_native_modules: {
        field: "PS_AUTOUP_CUSTOM_MOD_DESACT",
        value: true,
      },
      regenerate_email_templates: {
        field: "PS_AUTOUP_REGEN_EMAIL",
        value: true,
      },
      disable_all_overrides: {
        field: "PS_DISABLE_OVERRIDES",
        value: false,
      },
    },
    step_parent_id: "ua_container",
    stepper_parent_id: "stepper_content",
    form_route_to_save: "update-step-update-options-save-option",
    form_route_to_submit: "update-step-update-options-submit-form",
    error: {
      PS_AUTOUP_REGEN_EMAIL:
        "Example of an error that occured when switching the value!",
    },
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    // Stepper
    ...Stepper.args,
  },
};
