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

import DialogUpdateNotification from "../../../../views/templates/hooks/dialog-update-notification.html.twig";

export default {
  component: DialogUpdateNotification,
  title: "Hooks/hookDisplayBackOfficeHeader/Dialog update notification",
  parameters: {
    storyContext: 'STANDALONE',
  },
  argTypes: {
    version_type: {
      control: 'select' ,
      options: ['major', 'minor', 'patch'],
    },
  },
  args: {
    'version_type': 'major',
    'version': '9.0.0',
    'contact_expert_url': 'https://experts.prestashop.com/english/experts/',
    'update_link': '#',
    'release_note': '#',
  },
  play: async () => {
    const dialog = document.getElementById('dialog-update-notification');
    if (dialog) {
      dialog.showModal();
    }
  },
};

export const Major = {
  args: {
    'version_type': 'major',
  }
};

export const Minor = {
  args: {
    'version_type': 'minor',
  }
};

export const Patch = {
  args: {
    'version_type': 'patch',
  }
};
