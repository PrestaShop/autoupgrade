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
import LocalArchive from "./LocalArchive.stories";
import CheckRequirements from "./CheckRequirements.stories";

export default {
  component: RadioCard,
  title: "Components/Radio card",
  argTypes: {
    badgeStatus: {
      control: "select",
      options: ["major", "minor", "patch"],
    },
  },
  args: {
    radioCardId: "",
    checked: false,
    title: "Update your store",
    message:
      "Update your store to benefit from the latest improvements, bug fixes and security patches.",
    disabled: false,
    disabledMessage: "No backup file found on your store.",
    badgeLabel: "Major version",
    badgeStatus: "major",
    releaseNote: "https://github.com/PrestaShop/autoupgrade",
    archiveCard: false,
    checkRequirements: false,
    ...LocalArchive.args,
    ...CheckRequirements.args,
  },
};

export const Default = {};

export const Archive = {
  args: {
    radioCardId: "",
    checked: true,
    title: "Local archive",
    message:
      "Save the archive file of the version you want to update to in the following directory: /admin/autoUpdate/download/",
    disabled: false,
    disabledMessage: "No backup file found on your store.",
    badgeLabel: "",
    releaseNote: "",
    archiveCard: true,
    checkRequirements: false,
    ...LocalArchive.args,
    ...CheckRequirements.args,
  },
};

export const Requirements = {
  args: {
    checked: true,
    checkRequirements: true,
    ...LocalArchive.args,
    ...CheckRequirements.args,
    requirementsOk: false,
    checkingForRequirements: false,
  },
};

document.addEventListener("DOMContentLoaded", () => {
  const radioCards = document.querySelectorAll(
    '.radio-card input[type="radio"]',
  );

  radioCards.forEach((radio) => {
    radio.addEventListener("click", function () {
      radioCards.forEach((radio) => {
        if (radio !== this) {
          radio.checked = false;
        }
      });
    });
  });
});
