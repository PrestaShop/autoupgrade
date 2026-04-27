/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import { maskSensitiveInfoInUrl } from '../../src/ts/appUI/utils/urlUtils';

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
