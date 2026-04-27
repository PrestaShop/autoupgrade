/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import api from '../api/RequestHandler';
import ToolbarHelpLink from '../components/ToolbarHelpLink';

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
    window.addEventListener('load', () => this.#updateToolbarHelpLink());
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

  /**
   * @private
   * @description Updates the behavior of the toolbar help link to disable the default documentation sidebar opening
   * and allow adding a 'target="_blank"' attribute for external links.
   */
  #updateToolbarHelpLink(): void {
    const toolbarHelpLink = new ToolbarHelpLink();
    toolbarHelpLink.updateHelpLink();
  }
}
