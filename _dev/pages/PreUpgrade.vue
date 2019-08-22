<template>
  <div>
    <steps :items="steps" />

    <div class="pre-upgrade-block">
      <h2>{{ $t('preUpgrade.title') }}</h2>
      <p>{{ $t('preUpgrade.description') }}</p>

      <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('backup')">{{ getIconIcon('backup') }}</i>
            {{ $t('Make a full backjup of your store') }}
          </span>

          <button class="btn btn-sm btn-default" type="button">
            <i class="material-icons">save_alt</i> {{ $t('One click back-up') }}
          </button>
        </li>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('backup')">{{ getIconIcon('backup') }}</i>
            {{ $t('Your store is in maintenance mode') }}
          </span>

          <button class="btn btn-sm btn-default" type="button">
            <i class="material-icons">autorenew</i> {{ $t('Switch to maintenance mode') }}
          </button>
        </li>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('backup')">{{ getIconIcon('backup') }}</i>
            {{ $t('PHP\'s "max_execution_time" setting has a high value or is disabled entirely (current value: unlimited)') }}
          </span>
        </li>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('backup')">{{ getIconIcon('backup') }}</i>
            {{ $t('Your store\'s root directory is writable (with appropriate permissions)') }}
          </span>
        </li>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('backup')">{{ getIconIcon('backup') }}</i>
            {{ $t('PHP "allow_url_fopen" option is turned on, or cURL is installed') }}
          </span>
        </li>
      </ul>
    </div>


    <div class="pre-upgrade-block">
      <h2><i class="material-icons">warning</i>{{ $t('preUpgrade.modules.title') }}</h2>
      <p v-html="$t('preUpgrade.modulesdescription')" />

      <ul>
        <li>
          <checkbox /> I understand that ...
        </li>
      </ul>
      <button @click="disabledAllModules" class="btn btn-primary btn-sm" disabled>
        {{ $t('preUpgrade.buttons.disableModules') }}
      </button>
    </div>

    <div class="pre-upgrade-block row">
      <div class="col-md-6">
        <h2><i class="material-icons">warning</i>{{ $t('preUpgrade.modules.title') }}</h2>
        <p v-html="$t('preUpgrade.modulesdescription')" />

        <ul>
          <li>
            <checkbox /> I understand that not all my modules might be compatible with the verison I'm going to ugprade to.
          </li>
          <li>
            <checkbox /> I understand thatr some of my native modules might loose previous data after upgrade.
          </li>
        </ul>
      </div>
    </div>

    <button @click="runUpgradeProcess" class="btn btn-primary btn-block btn-lg" disabled>
      {{ $t('preUpgrade.buttons.upgrade') }}
    </button>
  </div>
</template>

<script>
  import Steps from '@/components/Steps';
  import Checkbox from '@/components/form/CheckBox';

  export default {
    name: 'Version',
    components: {
      Checkbox,
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
      };
    },
    mounted() {
      this.$store.dispatch('steps/setStep', 1);
    },
    methods: {
      runUpgradeProcess() {
        this.$router.push('/upgrade');
      },
      disabledAllModules() {
      },
      getIconClass(type) {
        if (type) {
          return 'icon-danger';
        }

        return 'icon-success';
      },

      getIconIcon(type) {
        if (type) {
          return 'clear';
        }

        return 'done';
      },
    },
  };
</script>

<style lang="scss">
  #autoupgrade {
    @import '@/assets/pre-upgrade.scss';
  }
</style>
