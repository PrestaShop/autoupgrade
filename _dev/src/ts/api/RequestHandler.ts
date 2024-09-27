import baseApi from './baseApi';
import { ApiResponse } from '../types/apiTypes';
import Hydration from '../utils/Hydration';

export default class RequestHandler {
  constructor() {
    window.AutoUpgrade.classes.RequestHandler = this;
  }

  public post(route: string, data = new FormData(), fromPopState?: boolean) {
    if (data) {
      data.append('dir', window.AutoUpgrade.variables.admin_dir);
    }

    baseApi
      .post('', data, {
        params: { route: route }
      })
      .then((response) => {
        const data = response.data as ApiResponse;
        this.handleResponse(data, fromPopState);
      });
  }

  private handleResponse(response: ApiResponse, fromPopState?: boolean) {
    if ('next_route' in response) {
      this.post(response.next_route);
    }
    if ('hydration' in response) {
      new Hydration().hydrate(response, fromPopState);
    }
  }
}
