<template>
  <label class="checkbox-switch">
    <span :class="{'label-checked': !val}" v-if="labelLeft">
      {{ labelLeft }}
    </span>

    <div class="switch-input" :class="{'-checked': val}">
      <input
        data-toggle="switch"
        data-inverse="true"
        type="checkbox"
        v-model="val"
      >
    </div>

    <span :class="{'label-checked': val}" v-if="labelRight">
      {{ labelRight }}
    </span>
  </label>
</template>

<script>
  export default {
    name: 'FormCheckBoxSwitch',
    props: {
      value: {
        type: Boolean,
        required: false,
        default: false,
      },
      labelLeft: {
        type: String,
        required: false,
        default: '',
      },
      labelRight: {
        type: String,
        required: false,
        default: '',
      },
    },
    data() {
      return {
        val: this.value,
      };
    },
    watch: {
      val: function setVal(val) {
        this.val = val;
        this.$emit('input', val);
      },
    },
  };
</script>


<style lang="scss">
  #autoupgrade {
    @import '@/assets/components/form/checkbox-switch.scss';
  }
</style>
