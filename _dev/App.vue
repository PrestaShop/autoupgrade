<template>
  <div id="autoupgrade">
    <router-view />
  </div>
</template>

<script>
  export default {
    name: 'Home',
  };
</script>

<style lang="scss">
  #autoupgrade {
    /**
     * Make this version of prestakit and bootstrap specific to
     * this version of autoupgrade
     */
    @import '~prestakit/scss/application';

    margin: -16px -10px 0 -11px;
    background: linear-gradient(180deg, $step-background 45px, $color-clear 45px);
  }

  #main {
    #content {
      background: $color-clear;
    }
  }
</style>
