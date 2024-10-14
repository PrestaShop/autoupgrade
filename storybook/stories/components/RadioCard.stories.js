/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

import RadioCard from "../../../views/templates/components/radio-card.html.twig";
import { Default as LocalArchive } from "./LocalArchive.stories";
import { Default as CheckRequirements } from "./CheckRequirements.stories";

export default {
  component: RadioCard,
  title: "Components/Radio card",
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
    message: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eros lacus, tincidunt egestas lacus ac, placerat eleifend eros.",
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
  },
};

export const Requirements = {
  args: {
    // Requirements
    ...Default.args,
    checked: true,
    enableRequirementsCheck: true,
  },
};
