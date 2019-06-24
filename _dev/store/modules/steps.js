/* eslint-disable no-shadow, no-param-reassign */
import * as types from '../mutation-types';

const state = () => ({
  type: null,
  step: 1,
});

const actions = {
  setType({commit}, type) {
    commit(
      types.STEPS_TYPE,
      type,
    );
  },
  setStep({commit}, {step}) {
    commit(
      types.STEPS_STEP,
      step,
    );
  },
};

const mutations = {
  [types.STEPS_TYPE](state, data) {
    state.type = data;
  },
  [types.STEPS_STEP](state, data) {
    state.step = data;
  },
};

export default {
  state,
  actions,
  mutations,
};
/* eslint-enable no-shadow, no-param-reassign */
