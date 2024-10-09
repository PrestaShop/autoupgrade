import { ApiResponseHydration } from '../types/apiTypes';
import { routeHandler, scriptHandler } from '../autoUpgrade';

export default class Hydration {
  public static hydrationEventName = 'hydrate';
  public hydrationEvent = new Event(Hydration.hydrationEventName);

  public hydrate(data: ApiResponseHydration, fromPopState?: boolean) {
    const elementToUpdate = document.getElementById(data.parent_to_update);

    if (elementToUpdate && data.new_content) {
      elementToUpdate.innerHTML = data.new_content;

      if (data.new_route) {
        scriptHandler.updateRouteScript(data.new_route);

        if (!fromPopState) {
          routeHandler.setNewRoute(data.new_route);
        }
      }

      elementToUpdate.dispatchEvent(this.hydrationEvent);
    }
  }
}
