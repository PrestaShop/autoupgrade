<template>
  <div class="btn-group">
    <button class="btn btn-primary dropdown-toggle" type="button" @click="toggleDisplay">
      <slot name="title">
        {{ displayedLabel }}
      </slot>
    </button>

    <div class="dropdown-menu" v-if="opened">
      <a
        class="dropdown-item"
        href="#"
        v-for="item in items"
        :key="item.name"
        @click.prevent="selectItem(item)"
      >
        {{ item.name }}
      </a>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'FormSelect',
    props: {
      value: {
        type: [String, Number],
        required: false,
        default: 0,
      },
      label: {
        type: String,
        required: false,
        default: null,
      },
      items: {
        type: Array,
        required: true,
      },
    },
    data() {
      return {
        opened: false,
        selectedItem: null,
      };
    },
    computed: {
      displayedLabel() {
        if (this.label === null && this.items.length > 0) {
          return this.items[0].name;
        }

        return this.selectedItem !== null ? this.selectedItem.name : this.label;
      },
    },
    mounted() {
      this.items.forEach((item) => {
        if (item.value === this.value) {
          this.selectedItem = item;
        }
      });
    },
    methods: {
      toggleDisplay() {
        this.opened = !this.opened;
      },
      selectItem(item) {
        this.selectedItem = item;
        this.$emit('input', item.value);
      },
    },
  };
</script>
