<template>
  <div class="steps-container">
    <ol class="steps">
      <step
        v-for="(item, index) in items"
        :key="index"
        :name="item.name"
        :active="getCurrentStep === index"
      >
        <slot>
          {{ item.name }}
        </slot>
      </step>
    </ol>
  </div>
</template>

<script>
  import {mapGetters} from 'vuex';

  import Step from './Step';

  export default {
    name: 'Steps',
    components: {
      Step,
    },
    props: {
      items: {
        type: Array,
        required: false,
        default() {
          return [
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
          ];
        },
      },
    },
    computed: {
      ...mapGetters(
        'steps',
        [
          'getCurrentStep',
        ],
      ),
    },
  };
</script>

<style lang="scss">
  #autoupgrade {
    @import '@/assets/components/steps.scss';
  }
</style>
