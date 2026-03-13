/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
        field: "disable_non_native_modules",
        value: true,
      },
      regenerate_email_templates: {
        field: "regenerate_email_templates",
        value: true,
      },
      disable_all_overrides: {
        field: "disable_overrides",
        value: false,
      },
    },
    step_parent_id: "ua_container",
    stepper_parent_id: "stepper_content",
    form_route_to_save: "update-step-update-options-save-option",
    form_route_to_submit: "update-step-update-options-submit-form",
    error: {
      regenerate_email_templates:
        "Example of an error that occured when switching the value!",
    },
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    // Stepper
    ...Stepper.args,
  },
};
