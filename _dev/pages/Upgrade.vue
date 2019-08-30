
<template>
  <div>
    <autoupgrade-header />

    <div class="upgrade-block au-block">
      <h2>{{ $t('upgrade.title') }}</h2>
      <p>{{ $t('upgrade.description') }}</p>

      <progress-bar
        v-model="progress"
      />
    </div>
  </div>
</template>

<script>
  import AutoupgradeHeader from '@/components/Header';
  import ProgressBar from '@/components/ProgressBar';

  export default {
    name: 'Version',
    components: {
      ProgressBar,
      AutoupgradeHeader,
    },
    data() {
      return {
        progress: 50,
      };
    },
    mounted() {
      this.$store.dispatch('steps/setStep', 2);
    },
    methods: {
    },
  };
</script>

<style lang="scss">
  #autoupgrade {
    @import '@/assets/upgrade.scss';
  }
</style>
