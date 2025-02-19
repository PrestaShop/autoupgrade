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
import { maskSensitiveInfoInUrl } from '../../../src/ts/appUI/utils/urlUtils';

describe('urlUtils', () => {
  describe('maskSensitiveInfoInUrl', () => {
    const adminFolder = 'admin-dev';

    test('URL with admin folder and token', () => {
      const url =
        'http://myshop.com/admin-dev/index.php?controller=AdminSelfUpgrade&token=831ecc0c2e1c41af40cee361afec03f3&route=home-page';
      expect(maskSensitiveInfoInUrl(url, adminFolder)).toBe(
        'http://myshop.com/********/index.php?controller=AdminSelfUpgrade&token=********&route=home-page'
      );
    });

    test('URL without token', () => {
      const url =
        'http://myshop.com/admin-dev/index.php?controller=AdminSelfUpgrade&route=home-page';
      expect(maskSensitiveInfoInUrl(url, adminFolder)).toBe(
        'http://myshop.com/********/index.php?controller=AdminSelfUpgrade&route=home-page'
      );
    });

    test('URL with token but without admin folder', () => {
      const url =
        'http://myshop.com/index.php?controller=AdminSelfUpgrade&token=831ecc0c2e1c41af40cee361afec03f3&route=home-page';
      expect(maskSensitiveInfoInUrl(url, adminFolder)).toBe(
        'http://myshop.com/index.php?controller=AdminSelfUpgrade&token=********&route=home-page'
      );
    });

    test('URL without admin folder & token', () => {
      const url = 'http://myshop.com/index.php?controller=AdminSelfUpgrade&route=home-page';
      expect(maskSensitiveInfoInUrl(url, adminFolder)).toBe(
        'http://myshop.com/index.php?controller=AdminSelfUpgrade&route=home-page'
      );
    });

    test('URL with multiple admin folder occurrence', () => {
      const url =
        'http://myshop.com/admin-dev/admin-dev/index.php?controller=AdminSelfUpgrade&token=831ecc0c2e1c41af40cee361afec03f3&route=home-page';
      expect(maskSensitiveInfoInUrl(url, adminFolder)).toBe(
        'http://myshop.com/********/********/index.php?controller=AdminSelfUpgrade&token=********&route=home-page'
      );
    });

    test('URL with empty token', () => {
      const url =
        'http://myshop.com/admin-dev/index.php?controller=AdminSelfUpgrade&token=&route=home-page';
      expect(maskSensitiveInfoInUrl(url, adminFolder)).toBe(
        'http://myshop.com/********/index.php?controller=AdminSelfUpgrade&token=********&route=home-page'
      );
    });

    test('URL with similar token parameter', () => {
      const url =
        'http://myshop.com/admin-dev/index.php?controller=AdminSelfUpgrade&mytoken=12345&route=home-page';
      expect(maskSensitiveInfoInUrl(url, adminFolder)).toBe(
        'http://myshop.com/********/index.php?controller=AdminSelfUpgrade&mytoken=12345&route=home-page'
      );
    });

    test('Case sensitivity for token parameter', () => {
      const url =
        'http://myshop.com/admin-dev/index.php?controller=AdminSelfUpgrade&TOKEN=831ecc0c2e1c41af40cee361afec03f3&route=home-page';
      expect(maskSensitiveInfoInUrl(url, adminFolder)).toBe(
        'http://myshop.com/********/index.php?controller=AdminSelfUpgrade&token=********&route=home-page'
      );
    });
  });
});
