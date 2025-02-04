import { AxiosError } from "axios";
import { ApiError, ApiResponseAction } from "../types/apiTypes";

export const toApiError = (error: AxiosError): ApiError => ({
  code: error.status,
  type: error.code,
  requestParams: error.request,
  additionalContents: formatResponseContents(error)
});

export const toApiResponseAction = (error: AxiosError): ApiResponseAction => ({
  kind: 'action',
  error: true,
  next: 'Error',
  stepDone: null,
  status: 'error',
  next_desc: 'Error',
  nextParams: {
    progressPercentage: 0
  },
  nextQuickInfo: [],
  apiError: toApiError(error)
});

export const isHttpErrorCode = (code?: number): boolean => {
  return typeof code === 'number' && code >= 300 && code.toString().length === 3;
}
const formatResponseContents = (error: AxiosError): string|undefined => {
  return typeof error.response?.data === 'string'
    ? error.response?.data
    : JSON.stringify(error.response?.data);
};


