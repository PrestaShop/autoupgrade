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

import CheckRequirements from "../../../views/templates/components/check-requirements.html.twig";

export default {
  component: CheckRequirements,
  title: "Components/Check requirements",
  args: {
    requirementsOk: true,
    updateAssistantDocs: 
      "https://devdocs.prestashop-project.org/8/basics/keeping-up-to-date/use-autoupgrade-module/",
    errors: [
      'Your current PHP version isn\'t compatible with PrestaShop 9.0.0. (Expected: 8.1 - 8.3 | Current: 8.0)',
      'Your store\'s root directory xxx isn\'t writable. Provide write access to the user running PHP with appropriate permission & ownership.',
      'The "/admin/autoupgrade" directory isn\'t writable. Provide write access to the user running PHP with appropriate permission & ownership.',
      'PHP\'s "Safe mode" needs to be disabled.',
      'Files can\'t be downloaded. Enable PHP\'s "allow_url_fopen" option or install PHP extension "cURL".',
      'Missing PHP extension "zip".',
      'Maintenance mode needs to be enabled. Enable maintenance mode and add your maintenance IP in Shop parameters > General > Maintenance.',
      'PrestaShop\'s caching features needs to be disabled. Disable caching features in Advanced parameters > Performance > Caching',
      'PHP\'s max_execution_time setting needs to have a high value or needs to be disabled entirely (current value: 15 seconds)',
      'Apache mod_rewrite needs to be enabled.',
      'The following PHP extensions needs to be installed: curl, dom, fileinfo, gd',
      'The following PHP functions needs to be allowed: fopen, fclose, fread, fwrite',
      'PHP memory_limit needs to be greater than 256 MB.',
      'PHP file_uploads configuration needs to be enabled.',
      'Unable to generate private keys using openssl_pkey_new. Check your OpenSSL configuration, especially the path to openssl.cafile.',
      'It\'s not possible to write in the following folders: xxx, yyy, zzz. Provide write access to the user running PHP with appropriate permission & ownership.',
      'The version of PrestaShop does not match the one stored in database. Your database structure may not be up-to-date and/or the value of PS_VERSION_DB needs to be updated in the configuration table.',
    ],
    warnings: [
      'Your current version of the module is out of date. Update now Modules > Module Manager > Updates',
      'We were unable to check your PHP compatibility with PrestaShop 9.0.0',
      'Some core files have been altered, customization made on these files will be lost during the update. See the list in Advanced parameters > Information',
    ],
  },
};

export const Default = {};
