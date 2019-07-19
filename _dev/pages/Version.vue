<template>
  <div>
    <steps :items="steps" />

    <div class="version-choice">
      <h2>{{ $t('version.title') }}</h2>
      <p>{{ $t('version.description') }}</p>

      <div class="version-choice-block">
        <div class="card">
          <div class="card-body">
            <h2 class="card-title">{{ $t('version.currentVersion') }}</h2>
            <p class="current-version">
              {{ currentVersion }}
            </p>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <h2 class="card-title">{{ $t('version.upgradeVersion') }}</h2>

            <dropdown v-model="selectedVersion" :items="[{value: '2.2.2', name: '2.2.2'}]" />

            <p class="current-version">
              <a href="#" @click.stop="whatsNew()">{{ $t('version.whatsNew') }}</a>
            </p>
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
  </div>
</template>

<script>
  import Steps from '@/components/Steps';
  import Dropdown from '@/components/form/Dropdown';
  import RadioSwitch from '@/components/form/RadioSwitch';

  export default {
    name: 'Version',
    components: {
      Dropdown,
      Steps,
      RadioSwitch,
    },
    data() {
      return {
        selectedVersion: null,
        currentVersion: '1.9.0.2',
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
  };
</script>

<style lang="scss">
  @import '@/assets/version.scss';

  .version-choice,
  .version-options-block {
    margin-top: 30px;
  }

  .version-choice-block {
    display: flex;
    justify-content: space-around;
  }
</style>
