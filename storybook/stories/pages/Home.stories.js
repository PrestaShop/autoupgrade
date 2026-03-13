/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import WelcomePage from "../../../views/templates/pages/home.html.twig";
import { Default as RadioCard } from "../components/RadioCard.stories";

export default {
  component: WelcomePage,
  id: "20",
  title: "Pages/Home",
};

export const Welcome = {
  args: {
    //Step
    badgeLabel: "",
    badgeStatus: "",
    releaseNote: "",
    ps_version: "ps_version",
    empty_backup: true,
    step_parent_id: "ua_container",
    form_route: "form_route",
    form_route_to_save: "update-step-version-choice-save-form",
    form_route_to_submit: "update-step-version-choice-submit-form",
    data_transparency_link:
      "https://www.prestashop-project.org/data-transparency",
    // Radio card
    ...RadioCard.args,
    form_fields: {
      route_choice: "",
    },
  },
};
