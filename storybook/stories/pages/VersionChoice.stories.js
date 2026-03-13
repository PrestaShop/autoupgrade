/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import VersionChoicePage from "../../../views/templates/pages/update.html.twig";
import { Online } from "../components/RadioCardOnline.stories";
import { Local } from "../components/RadioCardLocal.stories";
import { NoLocalArchive } from "../components/Alert.stories";
import { VersionChoice as Stepper } from "../components/Stepper.stories";
import { OnlineRecommended } from "../components/RadioCardOnlineRecommended.stories";

export default {
  component: VersionChoicePage,
  id: "31",
  title: "Pages/Update",
};

export const VersionChoice = {
  args: {
    // Step
    step: {
      code: "version-choice",
      title: "Version choice",
    },
    up_to_date: false,
    no_local_archive: true,
    current_prestashop_version: "8.1.6",
    current_php_version: "8.1",
    assets_base_path: "",
    step_parent_id: "ua_container",
    stepper_parent_id: "stepper_content",
    radio_card_online_parent_id: "radio_card_online",
    radio_card_archive_parent_id: "radio_card_archive",
    radio_card_online_recommended_parent_id: "radio_card_online_recommended",
    form_route_to_save: "update-step-version-choice-save-form",
    form_route_to_submit: "update-step-version-choice-submit-form",
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    // Radio cards
    ...Online.args,
    ...OnlineRecommended.args,
    ...Local.args,
    // Stepper
    ...Stepper.args,
    // Alert
    ...NoLocalArchive.args,
  },
};
