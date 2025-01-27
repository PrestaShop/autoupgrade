import { AxiosError, AxiosInstance, AxiosResponse } from 'axios';
import {
  ApiResponseUnknown,
  ApiResponseUnknownObject,
  APP_ERR_RESPONSE_BAD_TYPE,
  APP_ERR_RESPONSE_EMPTY,
  APP_ERR_RESPONSE_INVALID,
  SilencedApiError
} from '../types/apiTypes';

const responseFulfilledInterceptor = (response: AxiosResponse<ApiResponseUnknown, FormData>) => {
  if (!response?.data) {
    throw new AxiosError(
      'The response is empty',
      APP_ERR_RESPONSE_EMPTY,
      response.config,
      response.request,
      response
    );
  }
  // All responses must be a parsed JSON. If we get another type of response,
  // this means something went wrong, i.e Another software answered.
  if (Object.prototype.toString.call(response.data) !== '[object Object]') {
    throw new AxiosError(
      'The response does not have a valid type',
      APP_ERR_RESPONSE_BAD_TYPE,
      response.config,
      response.request,
      response
    );
  }

  // Make sure the response contains the expected data
  if (!(response.data as ApiResponseUnknownObject)?.kind) {
    throw new AxiosError(
      'The response contents is invalid',
      APP_ERR_RESPONSE_INVALID,
      response.config,
      response.request,
      response
    );
  }

  return response;
};

const responseErroredInterceptor = (error: Error) => {
  const errorSilenced = [AxiosError.ERR_CANCELED];
  // Ignore some errors
  if (error instanceof AxiosError && error.code && errorSilenced.includes(error.code)) {
    return Promise.reject(new SilencedApiError());
  }

  return Promise.reject(error);
};

export const addResponseInterceptor = (axios: AxiosInstance): void => {
  axios.interceptors.response.use(responseFulfilledInterceptor, responseErroredInterceptor);
};
