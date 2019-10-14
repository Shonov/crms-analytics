import { mapGetters } from 'vuex';
import echarts from 'echarts';

export default {
  computed: {
    ...mapGetters({
      amo: 'amocrm/statistic',
    }),
    dateMapGraph() {
      return Object.keys(this.amo.all).map(e => this.$moment(e).format('D. MMM'));
    },

    leadsChannelAttribution() {
      const statistic = this.amo.table;
      const dataset = Object.entries(statistic).map(([key, val]) => ({ name: `${key}`, value: val }));
      return {
        tooltip: {
          trigger: 'item',
          formatter: '{b} ({d}%)',
        },
        legend: {
          orient: 'vertical',
          left: 'left',
          data: dataset,
          formatter: name => `${name} (${statistic[name]})`,
        },
        series: [
          {
            type: 'pie',
            radius: '70%',
            center: ['50%', '50%'],
            data: dataset,
            orient: 'vertical',
            label: {
              formatter: '{b}: {@2012} ({d}%)',
            },
            labelLine: {
              smooth: 0.2,
            },
            itemStyle: {
              emphasis: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)',
              },
            },
          },
        ],
      };
    },

    leadsCADays() {
      const statistic = Object.values(this.amo.daily);
      let data = [];
      let legendData = [];

      if (statistic.length > 0) {
        statistic.forEach((e) => {
          Object.entries(e).forEach((item, index) => {
            if (item[0] !== 'date') {
              if (data[item[0]]) {
                data[item[0]].data.push(item[1]);
              } else {
                legendData[index - 1] = item[0];
                data[item[0]] = {
                  name: item[0],
                  type: 'line',
                  stack: `lead${index}`,
                  data: [item[1] ? item[1] : 0],
                };
              }
            }
          });
        });
        data = Object.values(data);
      } else {
        legendData = null;
        data = null;
      }
      return {
        tooltip: {
          trigger: 'axis',
        },
        legend: {
          data: legendData,
        },
        grid: {
          left: '2%',
          right: '3%',
          bottom: '3%',
          containLabel: true,
        },
        toolbox: {
          feature: {
            saveAsImage: {},
          },
        },
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: Object.keys(this.amo.daily).map(e => this.$moment(e).format('D. MMM')),
        },
        yAxis: {
          type: 'value',
        },
        series: data,
      };
    },

    allLeads() {
      const data = this.amo.all;

      return {
        legend: {
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
          data: Object.values(data).map(e => (this.$moment(e.date).format('D. MMM'))),
        },
        yAxis: {
          inverse: false,
          splitArea: { show: false },
        },
        series: [
          {
            name: 'Leads Count',
            type: 'bar',
            stack: 'one',
            itemStyle: {
              normal: {},
              emphasis: {
                barBorderWidth: 1,
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowOffsetY: 0,
                shadowColor: 'rgba(0,0,0,0.5)',
              },
            },
            data: Object.values(data).map(e => e.count),
            label: {
              normal: {
                show: true,
                position: 'inside',
              },
            },
          },
        ],
      };
    },

    operatorsTasks() {
      const operators = this.getTasks();

      if (!this.operators || this.operators.length === 0) {
        return {};
      }

      const itemStyle = this.itemStyle;
      const seriesLabel = this.seriesLabel;
      const tasks = this.amo.tasks.graph;

      let data = [];

      data = operators.map(operator => ({
        name: operator.name,
        type: 'bar',
        stack: 'one',
        itemStyle,
        data: tasks[operator.id].map(e => ((e.not_closed === 0) ? null : e.not_closed)),
        label: seriesLabel,
      }));

      return {
        legend: {
          data: operators.map(e => e.name),
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
          data: tasks[operators[0].id].map(e => this.$moment(e.date).format('D. MMM')),
        },
        yAxis: {
          inverse: false,
          splitArea: { show: false },
        },
        series: data,
      };
    },

    allOperators() {
      return this.amo.operators;
    },
  },
  data() {
    return {
      operators: '',
      itemStyle: {
        normal: {},
        emphasis: {
          barBorderWidth: 1,
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowOffsetY: 0,
          shadowColor: 'rgba(0,0,0,0.5)',
        },
      },
      seriesLabel: {
        normal: {
          show: true,
          position: 'inside',
        },
      },
    };
  },

  mounted() {},

  methods: {
    async dispatchAmocrmStatistic() {
      await this.$store.dispatch('amocrm/getStatistics', {
        params: this.getRequestData(),
      }).then(() => {
        this.operators = Object.values(this.allOperators).map(e => e.id);
      });
    },

    getTasks() {
      const operators = this.allOperators;

      if (this.operators === '' || this.operators.length === 0) {
        return false;
      }

      return this.operators.map(e => operators.find(x => x.id === e));
    },

    changeOperators(e) {
      if (e.length === 0) {
        this.operators = Object.values(this.allOperators).map(item => item.id);
      }
    },

    confirmOperators() {
      const chart = document.getElementById('tasks');
      const graph = echarts.getInstanceByDom(chart);
      graph.setOption(this.operatorsTasks);
    },
  },
};
