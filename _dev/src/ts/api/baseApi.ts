import axios from 'axios';

const baseApi = axios.create({
  baseURL: `${window.AutoUpgrade.admin_url}/autoupgrade/ajax-upgradetab.php`,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    Authorization: `Bearer ${() => window.AutoUpgrade.token}`
  }
});

export default baseApi;
