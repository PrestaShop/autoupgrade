import Vue from 'vue';

let binded = [];

function handler(e) {
  binded.forEach((el) => {
    if (!el.node.contains(e.target)) {
      el.callback(e);
    }
  });
}

function addListener(node, callback) {
  if (!binded.length) {
    document.addEventListener('click', handler, false);
  }

  binded.push({node, callback});
}

function removeListener(node, callback) {
  binded = binded.filter((el) => {
    if (el.node !== node) {
      return true;
    }

    if (!callback) {
      return false;
    }

    return el.callback !== callback;
  });
  if (!binded.length) {
    document.removeEventListener('click', handler, false);
  }
}

Vue.directive('click-outside', {
  bind(el, binding) {
    removeListener(el, binding.value);
    if (typeof binding.value === 'function') {
      addListener(el, binding.value);
    }
  },
  update(el, binding) {
    if (binding.value !== binding.oldValue) {
      removeListener(el, binding.oldValue);
      addListener(el, binding.value);
    }
  },
  unbind(el, binding) {
    removeListener(el, binding.value);
  },
});
