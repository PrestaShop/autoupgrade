<template>
  <div class="form-group">
    <label>{{ label }}</label>

    <span class="ps-switch ps-switch-sm">
      <input
        type="radio"
        v-model="val"
        :value="false"
      >
      <label @click="setValue(false)">{{ $t('Off') }}</label>
      <input
        type="radio"
        v-model="val"
        :value="true"
        checked
      >
      <label @click="setValue(true)">{{ $t('on') }}</label>
      <span class="slide-button" />
    </span>
    <small class="form-text" v-if="help.length !== 0">
      {{ help }}
    </small>
  </div>
</template>

<script>
  export default {
    name: 'FormSwitch',
    props: {
      label: {
        type: String,
        required: true,
      },
      help: {
        type: String,
        required: false,
        default: '',
      },
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
    methods: {
      setValue(value) {
        this.val = value;
        this.$emit('input', value);
      },
    },
  };
</script>
