import Vue from 'vue';
import Router from 'vue-router';

import Index from '@/pages/Index';
import PreUpgrade from '@/pages/PreUpgrade';
import Upgrade from '@/pages/Upgrade';
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
    {
      path: '/pre-upgrade',
      name: 'pre-upgrade',
      component: PreUpgrade,
    },
    {
      path: '/upgrade',
      name: 'upgrade',
      component: Upgrade,
    },
  ],
});
