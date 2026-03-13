/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import RadioCard from "../../../views/templates/components/radio-card.html.twig";
import { Default as LocalArchive } from "./LocalArchive.stories";
import { Default as CheckRequirements } from "./CheckRequirements.stories";

export default {
  component: RadioCard,
  title: "Components/Radio card",
  excludeStories: ["Default"],
  argTypes: {
    badgeStatus: {
      control: "select",
      options: ["major", "minor", "patch"],
    },
  },
};

export const Default = {
  args: {
    // Local archive
    ...LocalArchive.args,
    // Requirements
    ...CheckRequirements.args,
    radioCardId: "",
    radioName: "",
    radioValue: "",
    checked: false,
    required: false,
    title: "Radio card title",
    message:
      "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros lacus, tincidunt egestas lacus ac, placerat eleifend eros.",
    disabled: false,
    disabledMessage: "",
    badgeLabel: "",
    badgeStatus: "",
    releaseNote: "",
    archiveCard: false,
    enableRequirementsCheck: false,
    form_options: {
      update_value: "update",
      restore_value: "restore",
    },
    recommended: false,
  },
};

export const Requirements = {
  args: {
    // Requirements
    ...Default.args,
    checked: true,
    enableRequirementsCheck: true,
    title: "PrestaShop 9.0.0",
    message:
      "The maximum version of PrestaShop to which you can update your store, based on its PHP version.",
    badgeLabel: "Major version",
    releaseNote: "#",
  },
};
