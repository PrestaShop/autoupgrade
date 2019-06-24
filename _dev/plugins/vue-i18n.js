import Vue from 'vue';
import VueI18n from 'vue-i18n';

Vue.use(VueI18n);

const locale = global.contextLocale;
const messages = JSON.parse(global.translations);

export default new VueI18n({
  locale,
  messages,
});
