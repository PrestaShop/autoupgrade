<template>
  <div class="md-checkbox md-checkbox-inline">
    <label>
      <input type="checkbox" v-model="val">
      <i class="md-checkbox-control" />
    </label>
  </div>
</template>

<script>
  export default {
    name: 'FormCheckBox',
    props: {
      value: {
        type: Boolean,
        required: false,
        default: false,
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
