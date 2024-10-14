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

import RadioCardLocal from "../../../views/templates/components/radio-card-local.html.twig";
import { Default as LocalArchive } from "./LocalArchive.stories";

export default {
  component: RadioCardLocal,
  title: "Components/Radio card",
};

export const Local = {
  args: {
    ...LocalArchive.args,
    disabled: false,
    disabledMessage: "No backup file found on your store.",
    required: false,
    badgeLabel: "",
    releaseNote: "",
    form_options: {
      online_value: false,
      local_value: false,
    },
    form_fields: {
      channel: "local",
      archive_zip: "local",
      archive_xml: "local",
    },
    current_values: {
      channel: "local",
      archive_zip: "local.zip",
      archive_xml: "local.xml",
    },
    local_archives: {
      zip: [
        "archive1.zip",
        "archive2.zip",
        "archive3.zip",
      ],
      xml: [
        "archive1.xml",
        "archive2.xml",
        "archive3.xml",
      ]
    },
    local_requirements: {
      requirements_ok: true,
      errors: [],
      warnings: [],
    },
  },
};
