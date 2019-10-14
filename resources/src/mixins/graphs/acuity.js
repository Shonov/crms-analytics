import { mapGetters } from 'vuex';

export default {
  computed: {
    ...mapGetters({
      acuity: 'acuity/statistic',
    }),
    mainTable() {
      const statistic = Object.values(this.acuity.statisticsByTypes);

      statistic.forEach((e, index) => {
        const allSessions = e.all;
        if (e.all !== 0) {
          Object.keys(e).forEach((key) => {
            if (key !== 'type' && key !== 'all' && e[key] !== 0) {
              statistic[index][key] = `${e[key].toString()} (${((e[key] / allSessions) * 100).toFixed(2)}%)`;
            }
          });
        }
      });

      return statistic;
    },
    allSessions() {
      return this.dataForEachTypeSession(this.acuity.all_sessions);
    },
    trialSessions() {
      return this.dataForEachTypeSession(this.acuity.trials);
    },
    membershipsSessions() {
      return this.dataForEachTypeSession(this.acuity.memberships);
    },
    homeSessions() {
      return this.dataForEachTypeSession(this.acuity.home_sessions);
    },
  },

  methods: {
    dataForEachTypeSession(data) {
      const itemStyle = {
        normal: {},
        emphasis: {
          barBorderWidth: 1,
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowOffsetY: 0,
          shadowColor: 'rgba(0,0,0,0.5)',
        },
      };
      const seriesLabel = {
        normal: {
          show: true,
          position: 'inside',
        },
      };

      return {
        legend: {
          data: ['Completed', 'Cancelled', 'No Show'],
          align: 'left',
          left: 10,
        },
        grid: {
          left: '2%',
          right: '3%',
          bottom: '3%',
          containLabel: true,
        },
        brush: {
          toolbox: ['rect', 'polygon', 'lineX', 'lineY', 'keep', 'clear'],
          xAxisIndex: 0,
        },
        toolbox: {
          feature: {
            magicType: {
              type: ['stack', 'tiled'],
            },
          },
        },
        tooltip: {},
        xAxis: {
          data: data.map(e => (this.$moment(e.date).format('D. MMM'))),
        },
        yAxis: {
          inverse: false,
          splitArea: { show: false },
        },
        series: [
          {
            name: 'Completed',
            type: 'bar',
            stack: 'one',
            itemStyle,
            data: data.map(e => ((e.completed === 0) ? null : e.completed)),
            label: seriesLabel,
          },
          {
            name: 'Cancelled',
            type: 'bar',
            stack: 'one',
            itemStyle,
            data: data.map(e => ((e.cancelled === 0) ? null : e.cancelled)),
            label: seriesLabel,
          },
          {
            name: 'No Show',
            type: 'bar',
            stack: 'one',
            itemStyle,
            data: data.map(e => ((e.no_show === 0) ? null : e.no_show)),
            label: seriesLabel,
          },
        ],
      };
    },

    dispatchAcuityStatistic() {
      this.$store.dispatch('acuity/getStatistics', {
        params: this.getRequestData(),
      });
    },
  },
};
