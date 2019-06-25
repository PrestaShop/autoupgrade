<template>
  <li :class="{active: active}">
    {{ name }}
  </li>
</template>

<script>
  export default {
    name: 'Step',
    props: {
      name: {
        type: String,
        required: true,
      },
      active: {
        type: Boolean,
        required: false,
      },
    },
  };
</script>
