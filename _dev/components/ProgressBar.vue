<template>
  <div class="progress">
    <div
      class="progress-bar bg-primary"
      role="progressbar"
      :style="`width: ${computedValue}%`"
      :aria-valuenow="computedValue"
      aria-valuemin="0"
      aria-valuemax="100"
    >
      <slot />
    </div>
  </div>
</template>

<script>
  export default {
    name: 'ProgressBar',
    props: {
      value: {
        type: Number,
        required: true,
        default: 0,
      },
    },
    computed: {
      computedValue() {
        return Math.floor(this.value);
      },
    },
  };
</script>
