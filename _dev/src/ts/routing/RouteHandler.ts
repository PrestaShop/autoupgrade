import RequestHandler from '../api/RequestHandler';

export default class RouteHandler {
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
    if (newRoute !== null) {
      new RequestHandler().post(newRoute, new FormData(), true);
    }
  }
}
