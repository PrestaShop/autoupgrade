import Vue from 'vue';
import Router from 'vue-router';

import Index from '@/pages/Index';
import Version from '@/pages/Version';

Vue.use(Router);

export default new Router({
  routes: [
    {
      path: '/',
      name: 'index',
      component: Index,
    },
    {
      path: '/version',
      name: 'version',
      component: Version,
    },
  ],
});
