/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import RouteHandler from '../../src/ts/appUI/routing/RouteHandler';
import api from '../../src/ts/appUI/api/RequestHandler';

jest.mock('../../src/ts/appUI/api/RequestHandler', () => ({
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
