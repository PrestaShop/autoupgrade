/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
