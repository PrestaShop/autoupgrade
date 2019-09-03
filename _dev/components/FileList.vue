<template>
  <div class="file-list">
    <perfect-scrollbar>
      <ul>
        <li class="mb-2" v-for="file in files" :key="file">
          <i class="material-icons">warning</i>
          {{ file }}
        </li>
      </ul>
    </perfect-scrollbar>
  </div>
</template>

<script>
  import {PerfectScrollbar} from 'vue2-perfect-scrollbar';

  export default {
    name: 'Step',
    components: {
      PerfectScrollbar,
    },
    props: {
    },
    data() {
      return {
        files: [
          '.ac-php-conf.json',
          'AdminSelfUpgrade.gif',
          'AdminSelfUpgrade.gif',
          'classes',
          '_dev',
          'node_modules',
          'vendor',
          'AdminSelfUpgrade.php',
          'cli-rollback.php',
          'docker-compose.yml',
          'package.json',
          'views',
          'ajax-upgradetabconfig.php',
          'cli-upgrade.php',
          'functions.php',
          'package-lock.json',
          'vue.config.js',
          'ajax-upgradetab.php',
          'composer.json',
          'js',
          'Readme.md',
          'autoupgrade.php',
          'composer.lock',
          'logo.gif',
          'tests',
          'CHANGELOG.txt',
          'config.xml',
          'logo.png',
          'translations',
        ],
      };
    },
  };
</script>

<style lang="scss">
  @import '@/assets/components/file-list.scss';
</style>
