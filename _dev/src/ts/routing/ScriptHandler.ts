import HomePage from '../pages/HomePage';
import UpdatePageVersionChoice from '../pages/UpdatePageVersionChoice';
import UpdatePageUpdateOptions from '../pages/UpdatePageUpdateOptions';
import UpdatePageBackup from '../pages/UpdatePageBackup';
import UpdatePageUpdate from '../pages/UpdatePageUpdate';
import UpdatePagePostUpdate from '../pages/UpdatePagePostUpdate';
import PageAbstract from '../pages/PageAbstract';
import { RoutesMatching } from '../types/scriptHandlerTypes';
import { routeHandler } from '../autoUpgrade';

export default class ScriptHandler {
  constructor() {
    const currentRoute = routeHandler.getCurrentRoute();

    if (currentRoute) {
      this.loadScript(currentRoute);
    }
  }

  private currentScript: PageAbstract | undefined;

  private routesMatching: RoutesMatching = {
    'home-page': HomePage,
    'update-page-version-choice': UpdatePageVersionChoice,
    'update-page-update-options': UpdatePageUpdateOptions,
    'update-page-backup': UpdatePageBackup,
    'update-page-update': UpdatePageUpdate,
    'update-page-post-update': UpdatePagePostUpdate
  };

  private loadScript(routeName: string) {
    if (this.routesMatching[routeName]) {
      const pageClass = this.routesMatching[routeName];
      this.currentScript = new pageClass();
      this.currentScript.mount();
    }
  }

  public updateRouteScript(newRoute: string) {
    this.currentScript?.beforeDestroy();
    this.loadScript(newRoute);
  }
}
