import Vue from 'vue';
import './directives';
import i18n from './plugins/vue-i18n';
import App from './App';
import router from './router';
import store from './store';

Vue.config.productionTip = process.env.NODE_ENV === 'production';

new Vue({
  router,
  store,
  i18n,
  render: h => h(App),
}).$mount('#autoupgrade-vue-app');
