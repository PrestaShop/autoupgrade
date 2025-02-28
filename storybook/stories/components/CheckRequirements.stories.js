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

import CheckRequirements from "../../../views/templates/components/check-requirements.html.twig";

export default {
  component: CheckRequirements,
  title: "Components/Check requirements",
};

export const Default = {
  args: {
    dev_doc_upgrade_web_url: "https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/use-autoupgrade-module/",
    requirements: {
      requirements_ok: true,
      errors: [
        {
          message: 'Your current PHP version isn\'t compatible with PrestaShop 9.0.0. (Expected: 8.1 - 8.3 | Current: 8.0)'
        },
        {
          message: 'Your store\'s root directory xxx isn\'t writable. Provide write access to the user running PHP with appropriate permission & ownership.'
        },
        {
          message: 'The "/admin/autoupgrade" directory isn\'t writable. Provide write access to the user running PHP with appropriate permission & ownership.'
        },
        {
          message: 'PHP\'s "Safe mode" needs to be disabled.'
        },
        {
          message: 'Files can\'t be downloaded. Enable PHP\'s "allow_url_fopen" option or install PHP extension "cURL".'
        },
        {
          message: 'Missing PHP extension "zip".'
        },
        {
          message: 'Maintenance mode needs to be enabled. Enable maintenance mode and add your maintenance IP in <a class="link">Shop parameters > General > Maintenance</a>.'
        },
        {
          message: 'PrestaShop\'s caching features needs to be disabled. Disable caching features in <a class="link">Advanced parameters > Performance > Caching</a>'
        },
        {
          message: 'PHP\'s max_execution_time setting needs to have a high value or needs to be disabled entirely (current value: 15 seconds)'
        },
        {
          message: 'Apache mod_rewrite needs to be enabled.'
        },
        {
          message: 'The following PHP extensions needs to be installed: ',
          list: ['curl', 'dom', 'fileinfo', 'gd']
        },
        {
          message: 'The following PHP functions needs to be allowed: ',
          list: ['fopen', 'fclose', 'fread', 'fwrite']
        },
        {
          message: 'PHP memory_limit needs to be greater than 256 MB.'
        },
        {
          message: 'PHP file_uploads configuration needs to be enabled.'
        },
        {
          message: 'Unable to generate private keys using openssl_pkey_new. Check your OpenSSL configuration, especially the path to openssl.cafile.'
        },
        {
          message: 'It\'s not possible to write in the following folders: ',
          list: ['xxx', 'yyy', 'zzz'] 
        },
        {
          message: 'The version of PrestaShop does not match the one stored in database. Your database structure may not be up-to-date and/or the value of PS_VERSION_DB needs to be updated in the configuration table.'
        }
      ],
      warnings: [
        {
          message: 'Your current version of the module is out of date. Update now <a class="link">Modules > Module Manager > Updates</a>'
        },
        {
          message: 'We were unable to check your PHP compatibility with PrestaShop 9.0.0'
        },
        {
          message: 'Some core files have been altered, customization made on these files will be lost during the update. See the list in <a class="link">Advanced parameters > Information</a>'
        }
      ],
    },
  },
};
