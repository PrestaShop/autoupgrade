
<template>
  <div>
    <autoupgrade-header />

    <div class="post-upgrade-block au-block">
      <h2>{{ $t('postUpgrade.title') }}</h2>
      <p>{{ $t('postUpgrade.description') }}</p>

      <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons">
              arrow_forward
            </i>
            {{ $t('postUpgrade.list.backup') }}
          </span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons">
              arrow_forward
            </i>
            {{ $t('postUpgrade.list.backup') }}
          </span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons">
              arrow_forward
            </i>
            {{ $t('postUpgrade.list.backup') }}
          </span>
        </li>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span>
            <i class="material-icons">
              arrow_forward
            </i>
            {{ $t('postUpgrade.list.backup') }}
          </span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
  import AutoupgradeHeader from '@/components/Header';

  export default {
    name: 'Version',
    components: {
      AutoupgradeHeader,
    },
    mounted() {
      this.$store.dispatch('steps/setStep', 3);
    },
    methods: {
    },
  };
</script>

<style lang="scss">
  #autoupgrade {
    @import '@/assets/post-upgrade.scss';
  }
</style>
