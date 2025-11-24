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
