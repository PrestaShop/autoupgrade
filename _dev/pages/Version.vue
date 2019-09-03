<template>
  <div>
    <autoupgrade-header />

    <div class="version-choice au-block">
      <h2>{{ $t('version.title') }}</h2>
      <p>{{ $t('version.description') }}</p>

      <div class="version-choice-block">
        <div class="card card shadow mb-3 bg-white rounded">
          <div class="card-body">
            <h2 class="card-title">{{ $t('version.currentVersion') }}</h2>
            <p class="current-version">
              {{ currentVersion }}
            </p>
          </div>
        </div>

        <div class="card arrow-circle">
          <i class="material-icons">arrow_forward</i>
        </div>

        <div class="card shadow mb-3 bg-white rounded">
          <div class="card-body">
            <h2 class="card-title">{{ $t('version.upgradeVersion') }}</h2>

            <div class="select-version-block">
              <dropdown v-model="selectedVersion" :items="[{value: '2.2.2', name: '2.2.2'}]" />

              <p class="what-s-new">
                <a href="#" @click.stop="whatsNew()">{{ $t('version.whatsNew') }}</a>
              </p>
            </div>
          </div>
        </div>
      </div>

      <div class="version-options-block">
        <h2>{{ $t('version.options.title') }}</h2>

        <div class="card">
          <div class="card-body">
            <radio-switch
              :label="$t(`version.options.form.${name}.label`)"
              :help="$t(`version.options.form.${name}.description`)"
              v-model="form.options[name]"
              v-for="name in Object.keys(form.options)"
              :key="name"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="text-center m-4">
      <button @click="saveAndContinue" class="btn btn-primary">
        {{ $t('version.buttons.continue') }}
        <i class="material-icons">arrow_forward</i>
      </button>
    </div>
  </div>
</template>

<script>
  import AutoupgradeHeader from '@/components/Header';
  import Dropdown from '@/components/form/Dropdown';
  import RadioSwitch from '@/components/form/RadioSwitch';

  export default {
    name: 'Version',
    components: {
      Dropdown,
      AutoupgradeHeader,
      RadioSwitch,
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
      };
    },
    mounted() {
      this.currentVersion = '1.9.0.2';
      this.$store.dispatch('steps/setStep', 0);
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
