/**
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */
import axios from 'axios';

const baseApi = axios.create({
  baseURL: `${window.AutoUpgradeVariables.admin_url}/index.php?controller=AdminAutoupgradeAjax`,
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  },
  params: {
    ajax: 1,
    token: window.AutoUpgradeVariables.token
  }
});

export default baseApi;
