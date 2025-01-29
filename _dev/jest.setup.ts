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
import { TextEncoder, TextDecoder } from 'util';
// Needed to avoid error "ReferenceError: TextEncoder is not defined" when using JSDOM in tests
Object.assign(global, { TextDecoder, TextEncoder });

// We don't wait for the call to beforeAll to define window properties.
window.AutoUpgradeVariables = {
  token: 'test-token',
  admin_url: 'http://localhost',
  admin_dir: '/admin_directory',
  stepper_parent_id: 'stepper_content',
  module_version: '7.1.0',
  anonymous_id: 'b168a116d1a14fda8c21a22c7560fa27ade7dae22641ce9d773be680640dac0f',
  php_version: '7.4.33'
};

beforeAll(() => {});
