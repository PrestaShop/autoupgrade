<template>
  <header class="row">
    <div class="col-lg-8">
      <steps />
    </div>
    <div class="col-lg-4">
      <checkbox-switch
        :label-left="$t('header.switch.left')"
        :label-right="$t('header.switch.right')"
      />
    </div>
  </header>
</template>

<script>
  import Steps from '@/components/Steps';
  import CheckboxSwitch from '@/components/form/CheckboxSwitch';

  export default {
    name: 'Header',
    components: {
      Steps,
      CheckboxSwitch,
    },
  };
</script>
