<template>
  <div>
    <steps />
  </div>
</template>

<script>
  import Steps from '@/components/Steps';

  export default {
    name: 'Version',
    components: {
      Steps,
    },
  };
</script>

<style lang="scss">
  @import '@/assets/version.scss';
</style>
