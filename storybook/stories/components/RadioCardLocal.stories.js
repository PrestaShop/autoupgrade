/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import RadioCardLocal from "../../../views/templates/components/radio-card-local.html.twig";
import { Default as LocalArchive } from "./LocalArchive.stories";

export default {
  component: RadioCardLocal,
  title: "Components/Radio card",
};

export const Local = {
  args: {
    ...LocalArchive.args,
    updateAssistantDocs:
      "https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/use-autoupgrade-module/",
    disabled: false,
    disabledMessage: "No backup file found on your store.",
    required: false,
    badgeLabel: "",
    releaseNote: "",
    form_options: {
      online_value: false,
      online_recommended_value: false,
      local_value: false,
    },
    form_fields: {
      channel: "local",
      archive_zip: "archive_zip",
      archive_xml: "archive_xml",
    },
    current_values: {
      channel: "local",
      archive_zip: "local.zip",
      archive_xml: "local.xml",
    },
    local_archives: {
      zip: ["archive1.zip", "archive2.zip", "archive3.zip"],
      xml: ["archive1.xml", "archive2.xml", "archive3.xml"],
    },
    requirements: {
      requirements_ok: true,
      errors: [],
      warnings: [],
    },
    recommended: true,
  },
};
