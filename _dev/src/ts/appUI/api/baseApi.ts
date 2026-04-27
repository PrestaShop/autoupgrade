/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import axios from 'axios';
import { addRequestInterceptor } from './requestInterceptor';
import { addResponseInterceptor } from './responseInterceptor';

const baseApi = axios.create({
  baseURL: `${window.AutoUpgradeVariables.admin_url}/autoupgrade/ajax-upgradetab.php`,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Authorization: `Bearer ${() => window.AutoUpgradeVariables.token}`
  },
  transitional: {
    clarifyTimeoutError: true
  }
});

addRequestInterceptor(baseApi);
addResponseInterceptor(baseApi);

export default baseApi;
