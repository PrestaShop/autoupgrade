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

import LogsTemplates from "../../../views/templates/components/logs-templates.html.twig";

export default {
  component: LogsTemplates,
  title: "Components/LogsTemplates",
  includeStories: [],
};

export const Default = {
  args: {
    logs: [
      "DEBUG - Step UpdateInitialization",
      "INFO - Starting upgrade...",
      "INFO - Destination version: 8.2.0",
      "INFO - Shop deactivated. Now downloading... (this can take a while)",
      "DEBUG - Downloaded archive will come from https://api.prestashop-project.org/assets/prestashop/8.2.0/prestashop.zip",
      "DEBUG - MD5 hash will be checked against 5a203ec132fe3f50b24889bc91ffb8d9",
      "DEBUG - Step Download",
      "DEBUG - Downloading from https://api.prestashop-project.org/assets/prestashop/8.2.0/prestashop.zip",
      "DEBUG - File will be saved in /var/www/html/**admin_folder**/autoupgrade/download/prestashop.zip",
      "DEBUG - Download directory has been emptied",
      "DEBUG - Download complete.",
      "INFO - Download complete. Now extracting...",
      "DEBUG - Step Unzip",
      'DEBUG - "/latest" directory has been emptied',
      "DEBUG - Content of archive /var/www/html/**admin_folder**/autoupgrade/download/prestashop.zip is extracted",
      "DEBUG - Content of archive /var/www/html/**admin_folder**/autoupgrade/latest/prestashop.zip is extracted",
      "INFO - File extraction complete. Now updating files...",
      "DEBUG - Step UpdateFiles",
      "DEBUG - Generate diff file list between 1.7.8.11 and 8.2.0.",
      "INFO - 28121 files will be upgraded.",
      "DEBUG - Step UpdateFiles",
      "DEBUG - removed dir /**admin_folder**/themes/default/js/bundle/module.",
      "DEBUG - removed file /**admin_folder**/themes/default/js/vendor/jquery-passy.js.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/b46ff9f5c8bcba1ea3b4b320e22be5c7.svg.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/59b0f4c15b9b43ef643eefa44b5096f3.gif.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/5be71612af754cc566ec30ef200d8a65.woff.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/b073f5972d9c4cc1b8ae8e071e441376.woff2.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/33f225b8f5f7d6b34a0926f58f96c1e9.ttf.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/706450d7bba6374ca02fe167d86685cb.ttf.",
      "WARNING - removed file /**admin_folder**/themes/default/public/7be88e73fea7b64568a450d7c01346b0.woff.",
      "WARNING - removed file /**admin_folder**/themes/default/public/08952b029e4decbc8ef9fb553cae8cea.woff2.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/9f2144213fad53d4e0fdb26ecf93865f.woff.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/a282a2adada37bcc8a97c8113733e56c.png.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/theme.rtlfix.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/24aab533f87e7b434be5fa5b1684975c.svg.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/c36b5ac7c2dddf6f525c8d161412ef41.ttf.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/97493d3f11c0a3bd5cbd959f5d19b699.woff2.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/bundle.js.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/55835483c304eaa8477fea2c36abba17.woff2.",
      "ERROR - removed file /**admin_folder**/themes/default/public/39bfea5e86f5f41c9d2896dbbed6791b.woff.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/d9ee23d59d0e0e727b51368b458a0bff.woff.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/892667349c5cff6fcf7e40439596b97c.woff.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/ae8b1248595e70a828b880b9c56963da.eot.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/2980083682e94d33a66eef2e7d612519.svg.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/eb24af6668c633feab6bf8f989296e73.woff2.",
      "DEBUG - removed file /**admin_folder**/themes/default/public/40000000000000000000000000000000.woff2.",
    ],
  },
};
