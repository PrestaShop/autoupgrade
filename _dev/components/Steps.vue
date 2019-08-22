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
        required: true,
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

<style lang="scss" scoped>
  .steps-container {
    width: 100%
  }

  .steps {
    counter-reset: step;
    display: flex;
    justify-content: space-around;

    li {
      list-style-type: none;
      position: relative;
      text-align: center;
      width: 100%;
      color: $primary;

      &:before {
        position: relative;
        content: counter(step);
        counter-increment: step;
        width: $stepSize;
        height: $stepSize;
        line-height: $stepSize - 2;
        border: 2px solid $primary;
        display: block;
        text-align: center;
        margin: 0 auto 10px auto;
        border-radius: 50%;
        font-size: 1.05rem;
        font-weight: bold;
        background-color: $primary;
        color: #fff;
        z-index: 2;
      }
      &:after {
        content: '';
        position: absolute;
        width: 100%;
        height: 3px;
        background-color: $primary;
        top: ($stepSize / 2) - 2px;
        left: -50%;
        z-index: 0;
      }
      &:first-child {
        content: none;
        &:after {
          content: none;
        }
      }

      &.active {
        &:before {
          border-color: $primary;
          background-color: $primary;
          color: #fff;
        }

        & ~ li {
          &:before {
            background-color: #fff;
            color: $primary;
          }
          &:after {
            background: none;
          }
        }
      }
    }
  }
</style>
