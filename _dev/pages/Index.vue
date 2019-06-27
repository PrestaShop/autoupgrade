<template>
  <div id="welcome-block">
    <div class="panel">
      <h2>{{ $t('main.welcome') }}</h2>
      <p>
        {{ $t('main.intro') }}<br><br>
        {{ $t('main.rollback') }}br>
        {{ $t('main.hostProvider') }}<br>
        {{ $t('main.backup') }}
      </p>

      <button @click="setType('basic')" class="btn btn-primary btn-block btn-lg">
        {{ $t('main.choice.basic') }}
      </button>

      <button @click="setType('expert')" class="btn btn-outline-secondary btn-block btn-lg">
        {{ $t('main.choice.expert') }}
      </button>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Index',
    methods: {
      setType(step) {
        this.$store.dispatch('setType', {step});
        this.$router.push('/version');
      },
    },
  };
</script>

<style lang="scss">
  @import '@/assets/welcome.scss';
</style>
