<template>
  <div class="progressbar-container">
    <ol class="progressbar">
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
      },
    },
    computed: {
      ...mapGetters([
        'getCurrentStep',
      ]),
    },
  };
</script>

<style lang="scss" scoped>
  @import '@/assets/variables.scss';

  .progressbar-container {
    width: 100%
  }

  .progressbar {
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
        content: counter(step);
        counter-increment: step;
        width: $stepSize;
        height: $stepSize;
        line-height: $stepSize;
        border: 1px solid $primary;
        display: block;
        text-align: center;
        margin: 0 auto 10px auto;
        border-radius: 50%;
        font-size: 1.05rem;
        font-weight: bold;
        background-color: $primary;
        color: #fff;
      }
      &:after {
        content: '';
        position: absolute;
        width: 100%;
        height: 3px;
        background-color: $primary;
        top: ($stepSize / 2) - 2px;
        left: -50%;
        z-index: -1;
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
          background-color: #fff;
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
