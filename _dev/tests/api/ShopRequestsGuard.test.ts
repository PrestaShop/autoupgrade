/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

import { isRequestAllowed } from '../../src/ts/appUI/api/ShopRequestsGuard';

describe('ShopRequestsGuard', () => {
  describe('isRequestAllowed', () => {
    const dataSet = [
      {
        urlRequest: '/admin-dev/common/notifications/ack?_token=',
        urlShop:
          'http://localhost:8001/admin-dev/?controller=AdminSelfUpgrade&token=&route=restore-page-post-restore',
        expectedValue: false
      },
      {
        urlRequest: 'http://localhost:8001/admin-dev/common/notifications?_token=',
        urlShop:
          'http://localhost:8001/admin-dev/?controller=AdminSelfUpgrade&token=&route=restore-page-post-restore',
        expectedValue: false
      },
      {
        urlRequest:
          '/admin-dev/autoupgrade/ajax-upgradetab.php?route=restore-page-backup-selection',
        urlShop:
          'http://localhost:8001/admin-dev/?controller=AdminSelfUpgrade&token=&route=restore-page-post-restore',
        expectedValue: true
      },
      {
        urlRequest: 'https://api.prestashop-project.org/prestashop/stable',
        urlShop:
          'http://localhost:8001/admin-dev/?controller=AdminSelfUpgrade&token=&route=restore-page-post-restore',
        expectedValue: true
      }
    ];

    it.each(dataSet)(
      'checks the URLs can be called',
      ({
        urlRequest,
        urlShop,
        expectedValue
      }: {
        urlRequest: string;
        urlShop: string;
        expectedValue: boolean;
      }) => {
        (window as Window).location = urlShop;
        const result = isRequestAllowed(urlRequest);

        expect(result).toBe(expectedValue);
      }
    );
  });
});
