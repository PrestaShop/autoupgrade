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
import baseApi from './baseApi';
import { ApiResponse, ApiResponseAction } from '../types/apiTypes';
import Hydration from '../utils/Hydration';
import { AxiosError } from 'axios';

export class RequestHandler {
  #currentRequestAbortController: AbortController | null = null;

  /**
   * @description allows the current post call to be abort
   */
  public abortCurrentPost = (): void => {
    this.#currentRequestAbortController?.abort();
  };

  /**
   * @public
   * @param {string} route - Target route for the POST request.
   * @param {FormData}[data=new FormData()] - Form data to send with the request by default we send FormData with admin dir required by backend.
   * @param {boolean} [fromPopState] - Indicates if the request originated from a popstate event need by hydration.
   * @returns {Promise<void>}
   * @description Sends a POST request to the specified route with optional data and pop state indicator. Cancels any ongoing request before initiating a new one.
   */
  public async post(
    route: string,
    data?: FormData,
    fromPopState?: boolean
  ): Promise<void> {
    this.abortCurrentPost();

    // Create a new AbortController for the current request (used to cancel previous request)
    this.#currentRequestAbortController = new AbortController();
    const { signal } = this.#currentRequestAbortController;

    try {
      const response = await baseApi.post<ApiResponse>('', data, {
        params: { route },
        signal
      });

      const responseData = response.data;
      await this.#handleResponse(responseData, fromPopState);
    } catch (error) {
      if (error) {
        await this.#handleError(error as AxiosError);
      }
    }
  }

  /**
   * @public
   * @param {string} action - The action to be sent to the API.
   * @returns {Promise<ApiResponseAction | void>} - Resolves to the API response of type `ApiResponseAction` or `void` in case of an error.
   * @description Sends a POST request to the API with the specified action.
   *              Automatically includes the `admin_dir` required by the backend.
   */
  public async postAction(action: string): Promise<ApiResponseAction | void> {
    const data = new FormData();
    data.append('action', action);

    try {
      const response = await baseApi.post<ApiResponseAction>('', data);
      return response.data;
    } catch (error: unknown) {
      if (error instanceof AxiosError && error?.response?.data?.error) {
        return error.response.data as ApiResponseAction;
      }
      // TODO: catch errors
      console.error(error);
    }
  }

  /**
   * @private
   * @param {ApiResponse} response - The response data from the API.
   * @param {boolean} [fromPopState] - Indicates if the request originated from a popstate event need by hydration.
   * @returns {Promise<void>}
   * @description Handles the API response by checking for next route or hydration data.
   */
  async #handleResponse(response: ApiResponse, fromPopState?: boolean): Promise<void> {
    if ('next_route' in response) {
      await this.post(response.next_route);
    }
    if ('hydration' in response) {
      new Hydration().hydrate(response, fromPopState);
    }
  }

  async #handleError(error: AxiosError): Promise<void> {
    new Hydration().hydrateError({
      code: error.status,
      type: error.code,
      additionalContents: error.response?.data,
    });
  }
}

const api = new RequestHandler();

export default api;
