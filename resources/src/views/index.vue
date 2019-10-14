<template lang="pug">
  include ../tools/mixins.pug

  el-main.main
    el-container.section
      +b.EL-ROW(type="flex").param-container.row
        +b.EL-COL(:xs='24', :sm='24', :md="18").elective-data
          +e.item
            +e.SPAN.title Date range
            +e.EL-DATE-PICKER(v-model="datepicker"
              type="daterange" range-separator="-"
              start-placeholder="Start date"
              end-placeholder="End date"
              :picker-options="datePickerOptions"
              ).picker
          +e.item
            +e.SPAN.title Location
            +e.EL-SELECT(v-model="location" placeholder="Select location").location
              el-option(v-for="item in locations" :key="item.value" :label="item.label"
                :value="item.value")
        +b.EL-COL( :xs='8', :sm='8', :md="6").button-container
          +e.EL-BUTTON(type="primary" @click="dispatchAllStatistic").button Apply

    +b.EL-CONTAINER.section
      +e.title Acuity

    +b.EL-CONTAINER.section
      +b.EL-ROW(type="flex").row
        +b.EL-COL.total-data
          +e.title All Appointments
          +e.count {{ acuity.allAppointments  }}
        +b.EL-COL.total-data
          +e.title Completed Appointments
          +e.count {{ acuity.completedAppointments }}
        +b.EL-COL.total-data
          +e.title Cancelled Appointments
          +e.count {{ acuity.cancelledAppointments }}
        +b.EL-COL.total-data
          +e.title No Show Appointments
          +e.count {{ acuity.noShowAppointments }}
        +b.EL-COL.total-data
          +e.title Total Trials Booked for This Month
          +e.count {{ acuity.scheduledAppointments }}

    +b.EL-ROW.graph
      +e.title Overall session performance
      +b.EL-TABLE(:data="mainTable" border).main-table
        +e.EL-TABLE-COLUMN(prop="type" label="Session Type" value="").td
        +e.EL-TABLE-COLUMN(label="Appointments").td
          +e.EL-TABLE-COLUMN(prop="all" label="All" value="").td
          +e.EL-TABLE-COLUMN(prop="completed" label="Completed" value="").td
          +e.EL-TABLE-COLUMN(prop="cancelled" label="Cancelled" value="").td
          +e.EL-TABLE-COLUMN(prop="no_show" label="No Show" value="").td

    +b.EL-ROW.graph
      +e.title All sessions
      +e.V-CHART(:options="allSessions", autoresize).chart
    +b.EL-ROW.graph
      +e.title Trial sessions
      +e.V-CHART(:options="trialSessions", autoresize).chart
    +b.EL-ROW.graph
      +e.title Memberships sessions
      +e.V-CHART(:options="membershipsSessions", autoresize).chart
    +b.EL-ROW.graph
      +e.title Home sessions
      +e.V-CHART(:options="homeSessions", autoresize).chart

    +b.EL-CONTAINER.section
      +e.title Amocrm

    el-container.section
      +b.EL-ROW(type="flex").row
        +b.EL-COL.total-data
          +e.title Won leads
          +e.count {{ amo.won_and_lost.won }}
        +b.EL-COL.total-data
          +e.title Lost leads
          +e.count {{ amo.won_and_lost.lost }}

    +b.EL-ROW.graph
      +e.title Leads - Channel Attribution
      +e.V-CHART(:options="leadsChannelAttribution", autoresize).chart

    +b.EL-ROW.graph
      +e.title Leads - Channel Attribution (Every day)
      +e.V-CHART(:options="leadsCADays", autoresize).chart

    +b.EL-ROW.graph
      +e.title Overall Amocrm leads performance
      +e.V-CHART(:options="allLeads", autoresize).chart

    el-container.section(v-if="amo.tasks.all")
      +b.EL-ROW(type="flex").row
        +b.EL-COL.total-data
          +e.title New Tasks
          +e.count {{ amo.tasks.all.new }}
        +b.EL-COL.total-data
          +e.title Closed Tasks
          +e.count {{ amo.tasks.all.closed }}
        +b.EL-COL.total-data
          +e.title Not Closed Tasks
          +e.count {{ amo.tasks.all.not_closed }}

    +b.EL-ROW.graph
      +e.title Overdue Tasks
      +e.EL-SELECT(v-model="operators"
        clearable,
        multiple,
        collapse-tags,
        placeholder="Selected all operators",
        @change="changeOperators"
        @blur="confirmOperators"
        )
        +e.EL-OPTION(v-for="item in allOperators"
          :key="item.name"
          :label="item.name"
          :value="item.id")
      +e.V-CHART(:options="operatorsTasks", autoresize).chart#tasks

</template>
<script>
import Amocrm from '@/mixins/graphs/amocrm';
import Acuity from '@/mixins/graphs/acuity';
import config from '@/config/';

export default {
  name: 'mainPage',
  mixins: [Amocrm, Acuity],
  computed: {},
  data() {
    return {
      datepicker: null,
      datePickerOptions: {
        disabledDate(date) {
          let maxDate = new Date();
          maxDate = maxDate.setDate(maxDate.getDate() - 1);
          return date > maxDate;
        },
      },
      locations: Object.values(config.locations),
      location: config.locations.all,
    };
  },

  created() {
    this.location = this.locations[0];
  },
  mounted() {
    this.dispatchAllStatistic();
  },

  methods: {
    dispatchAllStatistic() {
      this.dispatchAcuityStatistic();
      this.dispatchAmocrmStatistic();
    },

    getRequestData() {
      if (this.location instanceof Object) {
        this.location = null;
      }

      let startDate;
      let lastDate;

      if (this.datepicker === null) {
        const dayStart = this.$moment().subtract(14, 'days');
        startDate = dayStart.format('YYYY-MM-DD');
        lastDate = new Date();
        lastDate = this.$moment(lastDate.setDate(lastDate.getDate() - 1)).format('YYYY-MM-DD');
      } else {
        startDate = this.$moment(this.datepicker[0]).format('YYYY-MM-DD');
        lastDate = this.$moment(this.datepicker[1]).format('YYYY-MM-DD');
      }

      this.datepicker = [new Date(startDate), new Date(lastDate)];

      return {
        location: this.location,
        start_day: startDate,
        last_day: lastDate,
      };
    },
  },
};
</script>

<style scoped>
  .echarts {
      width: 100%;
  }
</style>
