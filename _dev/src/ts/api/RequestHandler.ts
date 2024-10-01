import baseApi from './baseApi';
import { ApiResponse } from '../types/apiTypes';
import Hydration from '../utils/Hydration';

export class RequestHandler {
  public post(route: string, data = new FormData(), fromPopState?: boolean) {
    data.append('dir', window.AutoUpgradeVariables.admin_dir);

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

const api = new RequestHandler();

export default api;
