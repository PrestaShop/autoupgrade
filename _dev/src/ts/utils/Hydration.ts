import { ApiResponseHydration } from '../types/apiTypes';

export default class Hydration {
  public hydrate(data: ApiResponseHydration, fromPopState?: boolean) {
    const elementToUpdate = document.getElementById(data.parent_to_update);

    if (elementToUpdate && data.new_content) {
      elementToUpdate.innerHTML = data.new_content;

      if (data.new_route && window.AutoUpgrade.classes.ScriptHandler) {
        window.AutoUpgrade.classes.ScriptHandler.updateRouteScript(data.new_route);

        if (!fromPopState && window.AutoUpgrade.classes.RouteHandler) {
          window.AutoUpgrade.classes.RouteHandler.setNewRoute(data.new_route);
        }
      }
    }
  }
}
