// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.

// https://github.com/ecomfe/vue-echarts  https://echarts.baidu.com/
import ECharts from 'vue-echarts/components/ECharts';
import 'echarts/lib/chart/bar';
import 'echarts/lib/chart/line';
import 'echarts/lib/chart/pie';
import 'echarts/lib/chart/map';
import 'echarts/lib/chart/radar';
import 'echarts/lib/chart/scatter';
import 'echarts/lib/chart/effectScatter';
import 'echarts/lib/component/tooltip';
import 'echarts/lib/component/polar';
import 'echarts/lib/component/geo';
import 'echarts/lib/component/legend';
import 'echarts/lib/component/title';
import 'echarts/lib/component/visualMap';
import 'echarts/lib/component/dataset';

import Element from 'element-ui';
import elementLocale from 'element-ui/lib/locale/lang/en';
import 'element-ui/lib/theme-chalk/index.css';
import VueMoment from 'vue-moment';
import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';
import App from './App';
import router from './router';

// modules
import store from './store';

// config
import config from './config';

// css
import './scss/main.scss';

Vue.component('v-chart', ECharts);

Vue.prototype.moment = VueMoment;
Vue.use(VueMoment);
Vue.use(Element, { locale: elementLocale });
Vue.config.productionTip = false;

Vue.use(Vuex, axios);
axios.defaults.baseURL = config.apiUrl;

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  components: { App },
  template: '<App/>',
});
