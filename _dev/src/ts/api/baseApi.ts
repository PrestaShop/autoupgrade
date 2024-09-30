import axios from 'axios';

const baseApi = axios.create({
  baseURL: `${window.AutoUpgradeVariables.admin_url}/autoupgrade/ajax-upgradetab.php`,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Authorization: `Bearer ${() => window.AutoUpgradeVariables.token}`
  }
});

export default baseApi;
