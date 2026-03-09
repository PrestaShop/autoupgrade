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

import DialogTamperedFilesContents from "../../../views/templates/dialogs/dialog-tempered-files-content.html.twig";
import DialogTamperedFiles from "../../../views/templates/dialogs/dialog-tempered-files.html.twig";
import Dialog from '../../../views/templates/components/dialog.html.twig';
import { twig } from '@sensiolabs/storybook-symfony-webpack5';

export default {
  title: "Components/Dialog/ModulesReport",
  render: (args) => ({
    components: {DialogTamperedFiles, DialogTamperedFilesContents},
    template: twig`
      <twig:dialogs:dialog-modules-report>
        {% block dialog_extra_content %}
          {{ component('dialogs:dialog-modules-report-content', { incompatible_modules: incompatible_modules, uncertain_modules: uncertain_modules, module_manager_url: module_manager_url, prestashop_version: prestashop_version }) }}
        {% endblock %}
      </twig:dialogs:dialog-modules-report>
    `,
  }),
};

export const Loaded = {
  args: {
    title: "Some modules require your attention",
    incompatible_modules: [
      "ps_edition_basic",
    ],
    uncertain_modules: [
      'test_module_404',
      'test_module_405',
      'test_module_406',
      'test_module_407',
      'wololo',
      'ps_welcome',
    ],
    prestashop_version: '9.3.0',
    module_manager_url: '#',
    container_id: 'modules_report_container',
    content_action: '#',
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
