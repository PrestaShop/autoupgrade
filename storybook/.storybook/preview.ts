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

import { Preview, twig } from "@sensiolabs/storybook-symfony-webpack5";
import '../../_dev/src/scss/main.scss';

const cssEntrypoints = {
  "9.0.0": ["/9.0.0/default/theme.css"],
  "1.7.8.0": ["/1.7.8.0/default/theme.css"],
  "1.7.3.0": ["/1.7.3.0/default/theme.css"],
};
const availableBoThemes = Object.keys(cssEntrypoints);
const defaultBoTheme = availableBoThemes[0];

const preview: Preview = {
  parameters: {
    backgrounds: {
      disable: true,
    },
  },
  globalTypes: {
    backofficeTheme: {
      description: "PrestaShop Back-office theme",
      toolbar: {
        icon: "paintbrush",
        default: defaultBoTheme,
        dynamicTitle: true,
        items: availableBoThemes.map((prestashopVersion) => ({
          value: prestashopVersion,
          title: `PrestaShop ${prestashopVersion}`,
        })),
      },
    },
    _locale: {
      description: 'Internationalization locale',
      defaultValue: 'en',
      toolbar: {
        icon: 'globe',
        items: TRANSLATION_LOCALES.map((languageLocale) => ({
          value: languageLocale,
          title: new Intl.DisplayNames(
              [navigator.language || 'en'],
              {type: 'language'},
            ).of(languageLocale),
          right: String.fromCodePoint(...({'en': 'gb', 'cs': 'cz'}[languageLocale] || languageLocale)
            .toUpperCase()
            .split('')
            .map(char =>  127397 + char.charCodeAt())),
        })),
      },
    },
  },
  decorators: [
    (story, context) => {
      const selectedTheme = context.globals.backofficeTheme || defaultBoTheme;
      const cssContents = cssEntrypoints[selectedTheme];
      // Replace dots with hyphens and prepend 'v'
      const selectedThemeClass = `v${selectedTheme.replace(/\./g, '-')}`;

      const calledStory = story();
      calledStory.template = twig(`
        <div id="main">
          <div id="content" class="bootstrap update-assistant ${selectedThemeClass}">
            <div id="update_assistant">
              ${calledStory.template.getSource()}
              ${cssContents.map((cssFile) => `<link rel="stylesheet" type="text/css" href="${cssFile}" />`)}
            </div>
          </div>
        </div>
      `);
      return calledStory;
    },
  ],
};

export default preview;
