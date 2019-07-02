<template>
  <div
    class="btn-group"
    v-closable="closeHandler"
  >
    <button
      class="btn btn-primary dropdown-toggle"
      ref="dropdown-button"
      type="button"
      @click="toggleDisplay"
    >
      <slot name="title">
        {{ displayedLabel }}
      </slot>
    </button>

    <div class="dropdown-menu show" v-if="opened">
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
        closeHandler: {
          exclude: ['dropdown-button'],
          handler: 'onClose',
        },
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
      onClose() {
        this.opened = false;
      },
      toggleDisplay() {
        this.opened = !this.opened;
      },
      selectItem(item) {
        this.selectedItem = item;
        this.$emit('input', item.value);
        this.onClose();
      },
    },
  };
</script>
