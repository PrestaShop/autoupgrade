
<template>
  <div class="form-group row">
    <label class="col-sm-2 col-md-4 col-form-label">{{ label }}</label>

    <div class="col-sm-10 col-md-8">
      <span class="ps-switch ps-switch-sm">
        <input
          type="radio"
          v-model="val"
          :value="false"
        >
        <label @click="setValue(false)">{{ $t('off') }}</label>
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
