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
import RouteHandler from '../../../src/ts/appUI/routing/RouteHandler';
import api from '../../../src/ts/appUI/api/RequestHandler';

jest.mock('../../../src/ts/appUI/api/RequestHandler', () => ({
  post: jest.fn()
}));

describe('RouteHandler', () => {
  let routeHandler: RouteHandler;

  beforeEach(() => {
    jest.clearAllMocks();

    (window as Window).location = 'http://localhost/?route=home-page';

    routeHandler = new RouteHandler();
  });

  it('should set the new route if no current route is present', () => {
    window.history.pushState(null, '', 'http://localhost/');
    routeHandler = new RouteHandler();

    expect(window.location.search).toContain('route=home-page');
  });

  it('should retrieve the current route from URL query parameters', () => {
    const currentRoute = routeHandler.getCurrentRoute();
    expect(currentRoute).toBe('home-page');
  });

  it('should update the URL with the new route', () => {
    routeHandler.setNewRoute('update-page-version-choice');
    expect(window.location.search).toContain('route=update-page-version-choice');
  });

  it('should handle route change and call api.post when the route changes', () => {
    const newRoute = 'update-page-version-choice';
    (window as Window).location = `http://localhost/?route=${newRoute}`;

    const event = new Event('popstate');
    window.dispatchEvent(event);

    expect(api.post).toHaveBeenCalledWith(newRoute, expect.any(FormData), true);
  });
});
