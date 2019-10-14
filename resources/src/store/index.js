import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import acuity from '@/store/modules/acuity';
import amocrm from '@/store/modules/amocrm';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    acuity,
    amocrm,
  },
  getters: {
    HTTP: () => axios,
  },
  // strict: debug,
  // plugins: debug,
});
