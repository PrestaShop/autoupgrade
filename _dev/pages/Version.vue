<template>
  <div>
    <steps :items="steps" />

    <div class="version-choice">
      <div class="version-choice-blocks">
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

        <div class="version-options-block">
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
  .version-choice-blocks {
    display: flex;
    justify-content: space-around;
  }
</style>
