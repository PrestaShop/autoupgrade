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
interface ApiResponseHydration {
  kind: 'hydrate';
  hydration: boolean;
  new_content: string;
  new_route?: string;
  add_script?: string;
  parent_to_update: string;
}

interface ApiResponseNextRoute {
  kind: 'next_route';
  next_route: string;
}

interface ApiResponseAction {
  kind: 'action';
  error: null | boolean;
  stepDone: null | boolean;
  next: string;
  status: string;
  next_desc: null | string;
  nextQuickInfo: string[];
  nextErrors: string[];
  nextParams: {
    progressPercentage: number;
    [key: string]: unknown;
  };
}

export interface ApiError {
  code?: number;
  type?: string;
  requestParams?: XMLHttpRequest;
  additionalContents?: string | object;
}
export class SilencedApiError extends Error {}

export type ApiResponseUnknownObject = {
  kind?: Pick<ApiResponseHydration | ApiResponseNextRoute | ApiResponseAction, 'kind'>;
};
export type ApiResponseUnknown = string | ApiResponseUnknownObject | undefined;

type ApiResponse = ApiResponseHydration | ApiResponseNextRoute | ApiResponseAction;

export const APP_ERR_RESPONSE_BAD_TYPE = 'APP_ERR_RESPONSE_BAD_TYPE';
export const APP_ERR_RESPONSE_INVALID = 'APP_ERR_RESPONSE_INVALID';
export const APP_ERR_RESPONSE_EMPTY = 'APP_ERR_RESPONSE_EMPTY';

export type { ApiResponseHydration, ApiResponseNextRoute, ApiResponseAction, ApiResponse };
