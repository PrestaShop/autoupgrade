<template>
  <div>
    <steps />

    <div class="pre-upgrade-block">
      <h2>{{ $t('preUpgrade.title') }}</h2>
      <p>{{ $t('preUpgrade.description') }}</p>

      <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('backup')">
              {{ getIconIcon('backup') }}
            </i>
            {{ $t('preUpgrade.list.backup') }}
          </span>

          <button class="btn btn-sm btn-default" type="button">
            <i class="material-icons">save_alt</i> {{ $t('preUpgrade.buttons.backup') }}
          </button>
        </li>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('maintenance')">
              {{ getIconIcon('maintenance') }}
            </i>
            {{ $t('preUpgrade.list.maintenance') }}
          </span>

          <button class="btn btn-sm btn-default" type="button">
            <i class="material-icons">autorenew</i> {{ $t('preUpgrade.buttons.maintenance') }}
          </button>
        </li>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('max_execution_time')">
              {{ getIconIcon('max_execution_time') }}
            </i>
            {{ $t('preUpgrade.list.max_execution_time') }}
          </span>
        </li>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('is_writable')">
              {{ getIconIcon('is_writable') }}
            </i>
            {{ $t('preUpgrade.list.is_writable') }}
          </span>
        </li>

        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons" :class="getIconClass('allow_url_fopen')">
              {{ getIconIcon('allow_url_fopen') }}
            </i>
            {{ $t('preUpgrade.list.allow_url_fopen') }}
          </span>
        </li>
      </ul>
    </div>


    <div class="pre-upgrade-block">
      <h2><i class="material-icons">warning</i>{{ $t('preUpgrade.modules.title') }}</h2>
      <p v-html="$t('preUpgrade.modules.description')" />

      <ul class="checkbox-list">
        <li>
          <checkbox
            v-model="form.modules.compatibility"
            :label="$t('preUpgrade.modules.list.compatibility')"
          />
        </li>
        <li>
          <checkbox
            v-model="form.modules.native_modules"
            :label="$t('preUpgrade.modules.list.native_modules')"
          />
        </li>
        <li>
          <checkbox
            v-model="form.modules.experience"
            :label="$t('preUpgrade.modules.list.experience')"
          />
        </li>
      </ul>
      <div>
        <button
          @click="disabledAllModules"
          class="btn btn-primary btn-sm"
          :disabled="!formIsValid(form.modules)"
        >
          {{ $t('preUpgrade.buttons.disableModules') }}
        </button>

        {{ $t('preUpgrade.modules.help') }}
      </div>
    </div>

    <div class="pre-upgrade-block row">
      <div class="col-md-6">
        <h2><i class="material-icons">warning</i>{{ $t('preUpgrade.core.title') }}</h2>
        <p v-html="$t('preUpgrade.core.description')" />

        <ul class="checkbox-list">
          <li>
            <checkbox
              v-model="form.core.understand"
              :label="$t('preUpgrade.core.list.understand')"
            />
          </li>
        </ul>
      </div>

      <div class="col-md-6">
        FILE LIST
      </div>
    </div>

    <div class="text-center">
      <button
        @click="runUpgradeProcess"
        class="btn btn-primary"
        :disabled="!formIsValid(form.core)"
      >
        {{ $t('preUpgrade.buttons.upgrade') }}
      </button>
    </div>
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
          core: {
            understand: false,
          },
          modules: {
            compatibility: false,
            native_modules: false,
            experience: false,
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
      formIsValid(form) {
        /* eslint-disable-next-line no-restricted-syntax */
        for (const key in form) {
          if (form[key] !== true) {
            return false;
          }
        }

        return true;
      },
    },
  };
</script>

<style lang="scss">
  #autoupgrade {
    @import '@/assets/pre-upgrade.scss';
  }
</style>
