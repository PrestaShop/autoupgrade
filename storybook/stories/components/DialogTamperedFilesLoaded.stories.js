/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import DialogTamperedFilesContents from "../../../views/templates/dialogs/dialog-tempered-files-content.html.twig";
import DialogTamperedFiles from "../../../views/templates/dialogs/dialog-tempered-files.html.twig";
import { twig } from '@sensiolabs/storybook-symfony-webpack5';

export default {
  title: "Components/Dialog/TamperedFiles",
  render: (args) => ({
    components: {DialogTamperedFiles, DialogTamperedFilesContents},
    template: twig`
      <twig:dialogs:dialog-tempered-files>
        {% block dialog_extra_content %}
          {{ component('dialogs:dialog-tempered-files-content', { missing_files: missing_files, altered_files: altered_files }) }}
        {% endblock %}
      </twig:dialogs:dialog-tempered-files>
    `,
  }),
};

export const Loaded = {
  args: {
    title: "List of core alterations",
    message:
      "Some core files have been altered, customization made on these files will be lost during the update.",
    missing_files: [
      "adminProjetX/autoupgrade/index.php",
      "adminProjetX/backups/index.php",
      "config/xml/.htaccess",
      "config/xml/themes/index.php",
    ],
    altered_files: [
      "adminProjetX/themes/new-theme/public/tax.bundle.js",
      "adminProjetX/themes/new-theme/public/order_return_states_form.bundle.js",
      "adminProjetX/themes/new-theme/public/carrier.bundle.js",
      "adminProjetX/themes/new-theme/public/create_product_default_theme.css",
      "adminProjetX/themes/new-theme/public/meta.bundle.js",
      "adminProjetX/themes/new-theme/public/module.bundle.js",
      "adminProjetX/themes/new-theme/public/tax.bundle.js",
      "adminProjetX/themes/new-theme/public/order_return_states_form.bundle.js",
      "adminProjetX/themes/new-theme/public/carrier.bundle.js",
      "adminProjetX/themes/new-theme/public/create_product_default_theme.css",
      "adminProjetX/themes/new-theme/public/meta.bundle.js",
      "adminProjetX/themes/new-theme/public/module.bundle.js",
    ],
    container_id: 'tempered_files_container',
    content_action: '#',
  },
  play: async () => {
    const dialog = document.querySelector(".dialog");
    dialog.showModal();
  },
};
