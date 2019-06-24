import Vue from 'vue';
import Vuex from 'vuex';
import steps from './modules/steps';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

const createStore = () => new Vuex.Store({
  modules: {
    steps,
  },
  strict: debug,
});

export default createStore;
