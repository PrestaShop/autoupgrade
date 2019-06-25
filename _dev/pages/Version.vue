<template>
  <div>
    <steps :items="items" />

    <div class="version-choice">
      <div class="version-choice-block">
        <h2>{{ $t('version.currentVersion') }}</h2>
        <div class="version-choice-block">
          <p class="current-version">
            {{ currentVersion }}
          </p>
        </div>
      </div>

      <div class="version-choice-block">
        <h2>{{ $t('version.upgradeVersion') }}</h2>

        <div class="version-choice-block">
          <select v-model="selectedVersion">
            <option value="2.2.2">2.2.2</option>
          </select>

          <p class="current-version">
            <a href="#" @click.stop="whatsNew()">{{ $t('version.whatsNew') }}</a>
          </p>
        </div>
      </div>

      <div class="version-options-block">
        <h2>{{ $t('version.options.title') }}</h2>

        <div class="version-options-block">
          <div class="form-control" v-for="element in Object.keys(form.options)" :key="element">
            <label>{{ $t(`version.options.form.${element}.label`) }}</label>
            <input type="checkbox" v-model="form.options[element]">
            <p class="description-help">
              {{ $t(`version.options.form.$(element).descriptionHelp`) }}
            </p>
          </div>
        </div>
      </div>
    </div>
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
        currentVersion: '1.9.0.2',
        form: {
          options: {
            upgradeDefaultTheme: true,
            switchToDefaultTheme: false,
            keepCustomizedTemplates: false,
          },
        },
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
