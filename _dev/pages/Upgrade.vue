
<template>
  <div>
    <steps />

    <div class="upgrade-block">
      <h2>{{ $t('upgrade.title') }}</h2>
      <p>{{ $t('upgrade.description') }}</p>

      <progress-bar
        v-model="progress"
      />
    </div>
  </div>
</template>

<script>
  import Steps from '@/components/Steps';
  import ProgressBar from '@/components/ProgressBar';

  export default {
    name: 'Version',
    components: {
      ProgressBar,
      Steps,
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
