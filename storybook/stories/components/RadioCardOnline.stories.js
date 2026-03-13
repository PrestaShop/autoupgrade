/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import RadioCardOnline from "../../../views/templates/components/radio-card-online.html.twig";

export default {
  component: RadioCardOnline,
  title: "Components/Radio card",
};

export const Online = {
  args: {
    updateAssistantDocs:
      "https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/use-autoupgrade-module/",
    disabled: false,
    disabledMessage: "",
    form_options: {
      online_value: false,
      online_recommended_value: false,
      local_value: false,
    },
    form_fields: {
      channel: "online",
      archive_zip: "online",
      archive_xml: "online",
    },
    current_values: {
      channel: "online",
    },
    next_release: {
      badge_label: "Major version",
      badge_status: "major",
      release_note: "https://github.com/PrestaShop/autoupgrade",
      version: "9.0.0",
      recommended: false,
      message: 'The maximum version of PrestaShop to which you can update your store, based on its PHP version.',
    },
    next_releases: {
      online: {
        badge_label: "Major version",
        badge_status: "major",
        release_note: "https://github.com/PrestaShop/autoupgrade",
        version: "9.0.0",
        recommended: false,
        message: 'The maximum version of PrestaShop to which you can update your store, based on its PHP version.',
      },
      online_recommended: {
        badge_label: "Minor version",
        badge_status: "minor",
        release_note: "https://github.com/PrestaShop/autoupgrade",
        version: "8.2.3",
        recommended: true,
        message: 'The recommended version of PrestaShop to which you can update your store, based on its PHP version.',
      },
    },
    requirements: {
      requirements_ok: true,
      errors: [],
      warnings: [],
    },
    release_type: 'online',
    form_option_online_value: 'online',
  },
};
