/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import { ApiError, ApiResponseHydration } from '../types/apiTypes';
import { dialogContainer, routeHandler, scriptHandler } from '../main';
import { ScriptType } from '../types/scriptHandlerTypes';
import ErrorPage from '../pages/ErrorPage';

export default class Hydration {
  /**
   * @public
   * @static
   * @type {string}
   * @description The name of the hydration event.
   */
  public static hydrationEventName: string = 'hydrate';

  /**
   * @public
   * @type {Event}
   * @description The hydration event instance.
   */
  public hydrationEvent: Event = new Event(Hydration.hydrationEventName);

  public constructor() {
    dialogContainer.mount();
  }

  /**
   * @public
   * @param {ApiResponseHydration} data - The data containing new content and routing information.
   * @param {boolean} [fromPopState=false] - Indicates if the hydration is triggered from a popstate event.
   * @description Hydrates the specified element with new content and updates the route if necessary.
   */
  public hydrate(data: ApiResponseHydration, fromPopState?: boolean) {
    const elementToUpdate = document.getElementById(data.parent_to_update);

    if (elementToUpdate && data.new_content) {
      if (data.new_route) {
        scriptHandler.unloadScriptType(ScriptType.PAGE);
      }

      elementToUpdate.innerHTML = data.new_content;

      if (data.new_route) {
        scriptHandler.loadScript(data.new_route);

        if (!fromPopState) {
          routeHandler.setNewRoute(data.new_route);
        }
      }

      if (data.add_script) {
        scriptHandler.loadScript(data.add_script);
      }

      elementToUpdate.dispatchEvent(this.hydrationEvent);
    }
  }

  public hydrateError(error: ApiError): void {
    scriptHandler.unloadScriptType(ScriptType.PAGE);
    scriptHandler.loadScript('error-page');

    const elementToUpdate = document.getElementById(ErrorPage.templateId);
    elementToUpdate?.dispatchEvent(
      new CustomEvent(Hydration.hydrationEventName, { detail: error })
    );
  }
}
