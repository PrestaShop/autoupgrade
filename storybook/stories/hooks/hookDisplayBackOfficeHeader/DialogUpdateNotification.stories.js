/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
        'find_support_url': 'https://www.prestashop-project.org/support/',
        'update_link': '#',
        'release_note': '#',
        'dismiss_form_options': {
            '7_DAYS': '7_days',
            '30_DAYS': '30_days',
            'UNTIL_NEXT_RELEASE': 'until_next_release',
        },
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
