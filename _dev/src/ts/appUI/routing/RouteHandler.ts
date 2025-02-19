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
import api from '../api/RequestHandler';

export default class RouteHandler {
  /**
   * @constructor
   * @description Initializes the RouteHandler by setting the current route to 'home-page'
   *              if no route is present. Sets up an event listener for the 'popstate' event
   *              to handle route changes.
   */
  constructor() {
    this.#removeRedirectedParam();

    if (!this.getCurrentRoute()) {
      this.setNewRoute('home-page');
    }
    window.addEventListener('popstate', () => this.#handleRouteChange());
  }

  /**
   * @private
   * @returns {URL} The current URL object.
   * @description Retrieves the current URL of the window.
   */
  #getCurrentUrl(): URL {
    return new URL(window.location.href);
  }

  /**
   * @private
   * @returns {URLSearchParams} The URLSearchParams object containing the query parameters.
   * @description Retrieves the query parameters from the current URL.
   */
  #getQueryParams(): URLSearchParams {
    return this.#getCurrentUrl().searchParams;
  }

  /**
   * @public
   * @returns {string | null} The current route name, or null if not found.
   * @description Gets the current route from the query parameters.
   */
  public getCurrentRoute(): string | null {
    return this.#getQueryParams().get('route');
  }

  /**
   * @public
   * @param {string} newRoute - The new route name to set.
   * @description Sets a new route by updating the query parameters and pushing the new URL
   *              to the browser's history.
   */
  public setNewRoute(newRoute: string): void {
    const queryParams = this.#getQueryParams();
    queryParams.set('route', newRoute);

    const newUrl = `${this.#getCurrentUrl().pathname}?${queryParams.toString()}`;

    window.history.pushState(null, '', newUrl);
  }

  /**
   * @private
   * @async
   * @returns {Promise<void>} A promise that resolves when the request is complete.
   * @description Handles changes to the route by sending a POST request to the new route.
   *              If the new route is not null, it makes a request using the api module.
   */
  async #handleRouteChange() {
    const newRoute = this.getCurrentRoute();
    if (newRoute !== null) {
      await api.post(newRoute, new FormData(), true);
    }
  }

  /**
   * @private
   * @description Removes the '_redirected' query parameter from the URL if it exists.
   */
  #removeRedirectedParam(): void {
    const queryParams = this.#getQueryParams();

    if (queryParams.has('_redirected')) {
      queryParams.delete('_redirected');

      const newUrl = `${this.#getCurrentUrl().pathname}?${queryParams.toString()}`;
      window.history.replaceState(null, '', newUrl);
    }
  }
}
