<template>
  <div>
    <steps :items="steps" />

    <div>
      <h2>{{ $t('preUpgrade.title') }}</h2>
      <p>{{ $t('preUpgrade.description') }}</p>
    </div>

    <button @click="saveAndContinue" class="btn btn-primary btn-block btn-lg" disabled>
      {{ $t('preUpgrade.buttons.upgrade') }}
    </button>
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
        selectedVersion: null,
        currentVersion: null,
        form: {
          options: {
            upgradeDefaultTheme: true,
            switchToDefaultTheme: false,
            keepCustomizedTemplates: false,
          },
        },
        steps: [
          {
            name: this.$t('steps.choice'),
          },
          {
            name: this.$t('steps.prepare'),
          },
          {
            name: this.$t('steps.upgrade'),
          },
          {
            name: this.$t('steps.postUpgrade'),
          },
        ],
      };
    },
    mounted() {
      this.$store.dispatch('steps/setStep', 1);
    },
    methods: {
      saveAndContinue() {
        this.$router.push('/pre-upgrade');
      },
    },
  };
</script>

<style lang="scss">
  #autoupgrade {
    @import '@/assets/version.scss';
  }
</style>
