export default class RouteHandler {
  constructor() {
    window.AutoUpgrade.classes.RouteHandler = this;
    this.init();
  }

  private getCurrentUrl(): URL {
    return new URL(window.location.href);
  }

  private getQueryParams(): URLSearchParams {
    return this.getCurrentUrl().searchParams;
  }

  public getCurrentRoute(): string | null {
    return this.getQueryParams().get('route');
  }

  public setNewRoute(newRoute: string): void {
    const queryParams = this.getQueryParams();
    queryParams.set('route', newRoute);

    const newUrl = `${this.getCurrentUrl().pathname}?${queryParams.toString()}`;

    window.history.pushState(null, '', newUrl);
  }

  public init() {
    if (!this.getCurrentRoute()) {
      this.setNewRoute('home-page');
    }

    window.addEventListener('popstate', () => this.handleRouteChange());
  }

  private handleRouteChange() {
    const newRoute = this.getCurrentRoute();
    if (newRoute !== null && window.AutoUpgrade.classes.RequestHandler) {
      window.AutoUpgrade.classes.RequestHandler.post(newRoute, new FormData(), true);
    }
  }
}
