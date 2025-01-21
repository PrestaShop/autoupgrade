import { AxiosError, AxiosInstance, AxiosResponse } from "axios";
import { APP_ERR_RESPONSE_BAD_TYPE, APP_ERR_RESPONSE_INVALID } from "../types/apiTypes";

const responseFulfilledInterceptor = (response: AxiosResponse<any, FormData>) => {
  console.log('Checking response', response);

  // All responses must be a parsed JSON. If we get another type of response,
  // this means something went wrong, i.e Another software answered.
  if (Object.prototype.toString.call(response.data) !== '[object Object]') {
    throw new AxiosError('The response does not have a valid type', APP_ERR_RESPONSE_BAD_TYPE, response.config, response.request, response);
  }

  // Make sure the response contains the expected data
  if (!response.data.kind) {
    throw new AxiosError('The response contents is invalid', APP_ERR_RESPONSE_INVALID, response.config, response.request, response);
  }

  return response;
};

const responseErroredInterceptor = (error: any) => {
  const errorSilenced = [AxiosError.ERR_CANCELED];
  // Ignore some errors
  if (error instanceof AxiosError) {
    if (error.code && errorSilenced.includes(error.code)) {
      return Promise.reject(null);
    }
  }

  return Promise.reject(error);
}; 

export const addResponseInterceptor = (axios: AxiosInstance): void => {
  axios.interceptors.response.use(responseFulfilledInterceptor, responseErroredInterceptor);
}
