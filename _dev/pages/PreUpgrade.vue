
<template>
  <div>
    <autoupgrade-header />

    <div class="au-block pre-upgrade-block">
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

          <button class="btn btn-sm btn-outline-secondary" type="button">
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

          <button class="btn btn-sm btn-outline-secondary" type="button">
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


    <div class="pre-upgrade-block -even">
      <div class="au-block">
        <h2><i class="material-icons">warning</i>{{ $t('preUpgrade.modules.title') }}</h2>
        <p v-html="$t('preUpgrade.modules.description')" />

        <ul class="checkbox-list mt-4">
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

        <div class="mt-4">
          <button
            @click="disabledAllModules"
            class="btn btn-primary btn-sm mr-2"
            :disabled="!formIsValid(form.modules)"
          >
            {{ $t('preUpgrade.buttons.disableModules') }}
          </button>

          {{ $t('preUpgrade.modules.help') }}
        </div>
      </div>
    </div>

    <div class="au-block pre-upgrade-block row">
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
        <file-list />
      </div>
    </div>

    <div class="text-center">
      <button
        @click.prevent.stop="openModal"
        class="btn btn-primary"
        :disabled="!formIsValid(form.core)"
      >
        {{ $t('preUpgrade.buttons.upgrade') }}
      </button>
    </div>

    <modal
      v-if="isModalVisible"
      @close="closeModal"
      @confirm="runUpgradeProcess"
      confirmation
    >
      <template slot="body">
        <p class="text-center">
          <strong>{{ $t('preUpgrade.modal.start.title') }}</strong>
        </p>
        <p>
          {{ $t('preUpgrade.modal.start.description') }}
        </p>
      </template>
    </modal>
  </div>
</template>

<script>
  import FileList from '@/components/FileList';
  import AutoupgradeHeader from '@/components/Header';
  import Checkbox from '@/components/form/Checkbox';
  import Modal from '@/components/Modal';

  export default {
    name: 'Version',
    components: {
      Checkbox,
      FileList,
      Modal,
      AutoupgradeHeader,
    },
    data() {
      return {
        isModalVisible: false,
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
      openModal() {
        this.isModalVisible = true;
      },
      closeModal() {
        this.isModalVisible = false;
      },
      runUpgradeProcess() {
        this.closeModal();
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
