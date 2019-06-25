<template>
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

<style lang="scss">
  $normal: #ddd;
  $active: #0000FF;

  .container {
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
      color: $active;

      &:before {
        content: counter(step);
        counter-increment: step;
        width: 30px;
        height: 30px;
        line-height: 30px;
        border: 1px solid $active;
        display: block;
        text-align: center;
        margin: 0 auto 10px auto;
        border-radius: 50%;
        background-color: #fff;
      }
      &:after {
        content: '';
        position: absolute;
        width: 100%;
        height: 1px;
        background-color: $active;
        top: 15px;
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
        color: $active;
        &:before {
          border-color: $active;
        }

        & ~ li {
          color: $normal;
          border-color: $normal;
          &:before {
            border-color: $normal;
          }
          &:after {
            background-color: $normal;
          }
        }
      }
    }
  }
</style>
