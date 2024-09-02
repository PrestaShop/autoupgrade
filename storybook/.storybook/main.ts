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

import type { StorybookConfig } from "@sensiolabs/storybook-symfony-webpack5";
import webpack from "webpack";
import fs from 'fs';
import path from 'path';

const config: StorybookConfig = {
  stories: ["../stories/**/*.stories.[tj]s", "../stories/**/*.mdx"],
  addons: [
    "@storybook/addon-webpack5-compiler-swc",
    "@storybook/addon-links",
    "@storybook/addon-essentials",
  ],
  framework: {
    name: "@sensiolabs/storybook-symfony-webpack5",
    options: {
      // 👇 Here configure the framework
      symfony: {
        server: process.env.IS_IN_DOCKER
          ? "http://storybook-php:8000"
          : "http://localhost:8003",
        proxyPaths: ["/assets"],
        additionalWatchPaths: ["assets"],
      },
    },
  },
  webpackFinal: async (config) => {
    // List translations files on compilation to fill language selection list
    const newPlugin = new webpack.DefinePlugin({
      TRANSLATION_LOCALES: JSON.stringify(
        fs.readdirSync(path.resolve(__dirname, '../../translations'))
          .map((file) => new RegExp("^ModulesAutoupgradeAdmin.([a-z]+).xlf$", "i").exec(file)?.[1])
          .filter((locale) => !!locale)
      ),
    });
    if (config.plugins?.length) {
      config.plugins.push(newPlugin);
    } else {
      config.plugins = [newPlugin];
    }

    return config;
  },
  docs: {
    autodocs: "tag",
  },
  managerHead: (head) => `
        ${head}
        <link rel="stylesheet" type="text/css" href="/ibm-plex-sans.css" />
        <link rel="stylesheet" type="text/css" href="/theme.css" />
    `,
  previewBody: (body) => `
        <link rel="stylesheet" type="text/css" href="css/styles.css" />
        <link rel="stylesheet" type="text/css" href="/theme.css" />
        ${body}
    `,
  staticDirs: [
    "../public",
    "../node_modules/prestashop-bo-themes",
    { from: "../../css", to: "css" },
    { from: "../../js", to: "js" },
    { from: "../../img", to: "img" },
  ],
};
export default config;
