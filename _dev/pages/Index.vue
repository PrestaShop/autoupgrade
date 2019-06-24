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

      <router-link :to="{name: 'version', params: {type: 'basic'}}">
        {{ $t('main.choice.basic') }}
      </router-link>

      <router-link :to="{name: 'version', params: {type: 'expert'}}">
        {{ $t('main.choice.expert') }}
      </router-link>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'Index',
  };
</script>

<style lang="scss">
  @import '@/assets/welcome.scss';
</style>
