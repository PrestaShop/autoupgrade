import axios from 'axios';

const baseApi = axios.create({
  baseURL: `${window.AutoUpgrade.variables.admin_url}/autoupgrade/ajax-upgradetab.php`,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Authorization: `Bearer ${() => window.AutoUpgrade.variables.token}`
  }
});

export default baseApi;
