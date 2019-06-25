<template>
  <div>
    <steps :items="items" />
  </div>
</template>

<script>
  import Steps from '@/components/Steps';

  export default {
    name: 'Version',
    components: {
      Steps,
    },
    data() {
      return {
        items: [
          {
            name: this.$t('versions.choice'),
          },
          {
            name: this.$t('versions.prepare'),
          },
          {
            name: this.$t('versions.upgrade'),
          },
          {
            name: this.$t('versions.postUpgrade'),
          },
        ],
      };
    },
  };
</script>

<style lang="scss">
  @import '@/assets/version.scss';
</style>
