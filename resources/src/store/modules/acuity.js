export default {
  namespaced: true,

  state: {
    // general data
    allAppointments: false,
    completedAppointments: false,
    cancelledAppointments: false,
    noShowAppointments: false,
    scheduledAppointments: false,
    // data for each type
    all_sessions: [],
    home_sessions: [],
    memberships: [],
    trials: [],
    // data for main table
    statisticsByTypes: [],
    error: false,
  },
  getters: {
    statistic: state => state,
  },
  mutations: {
    STATISTIC_UPDATED: (state, response) => {
      state.allAppointments = response.data.all;
      state.completedAppointments = response.data.completed;
      state.cancelledAppointments = response.data.cancelled;
      state.noShowAppointments = response.data.no_show;
      state.scheduledAppointments = response.data.scheduledAppointments;
      state.all_sessions = Object.values(response.data.all_sessions);
      state.home_sessions = Object.values(response.data.home_sessions);
      state.memberships = Object.values(response.data.memberships);
      state.trials = Object.values(response.data.trials);
      state.statisticsByTypes = response.data.table;
      state.statisticsByTypes.all = {
        type: 'All Appointments',
        all: response.data.all,
        completed: response.data.completed,
        cancelled: response.data.cancelled,
        no_show: response.data.no_show,
      };
    },
    SET_ERROR: (state, error) => {
      state.error = error;
    },
  },
  actions: {
    getStatistics: ({ commit, rootGetters }, { params }) => {
      rootGetters.HTTP.get('/acuity', { params }).then((response) => {
        commit('STATISTIC_UPDATED', response.data);
      }, (err) => {
        commit('SET_ERROR', err);
      });
    },
  },
};
