export default {
  namespaced: true,

  state: {
    table: false,
    daily: false,
    all: false,
    error: false,
    won_and_lost: false,
    tasks: false,
    operators: false,
  },
  getters: {
    statistic: state => state,
  },
  mutations: {
    STATISTIC_UPDATED: (state, response) => {
      state.table = response.data.table;
      state.daily = response.data.daily_statistic;
      state.all = response.data.all_statistic;
      state.won_and_lost = response.data.won_and_lost;
      state.tasks = response.data.tasks;
      state.operators = Object.values(response.data.operators);
    },
    SET_ERROR: (state, error) => {
      state.error = error;
    },
  },
  actions: {
    getStatistics: async ({ commit, rootGetters }, { params }) => {
      await rootGetters.HTTP.get('/amocrm', { params }).then(async (response) => {
        commit('STATISTIC_UPDATED', response.data);
      }, (err) => {
        commit('SET_ERROR', err);
      });
    },
  },
};
