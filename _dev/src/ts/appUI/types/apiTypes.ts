/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
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
  nextParams: {
    progressPercentage: number;
    [key: string]: unknown;
  };
  apiError?: ApiError;
}

export interface ApiError {
  code?: number;
  type?: string;
  requestParams?: XMLHttpRequest;
  additionalContents?: string;
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
