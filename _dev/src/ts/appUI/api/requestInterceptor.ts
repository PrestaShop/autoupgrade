/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import { AxiosInstance, InternalAxiosRequestConfig } from 'axios';

const requestFulfilledInterceptor = (config: InternalAxiosRequestConfig<FormData>) => {
  if (!config.data) {
    config.data = new FormData();
  }
  config.data?.append('dir', window.AutoUpgradeVariables.admin_dir);
  return config;
};

export const addRequestInterceptor = (axios: AxiosInstance): void => {
  axios.interceptors.request.use(requestFulfilledInterceptor);
};
