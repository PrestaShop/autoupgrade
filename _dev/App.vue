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

    margin: 0 10%;
  }

  #main {
    #content {
      background: #ffffff;
    }
  }
</style>
