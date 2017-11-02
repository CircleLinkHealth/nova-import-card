<template>
  <div>
    <v-client-table ref="'tblCalls'" :data="tableData" :columns="columns" :options="options">
      <template slot="child_row" scope="props">
        <div>This will contain more row INFO</div>
      </template>
      <template slot="selected" scope="props">
        <input class="row-select" v-model="props.row.selected" type="checkbox" />
      </template>
      <template slot="h__selected" scope="props">
        <input class="row-select" v-model="selected" @change="toggleAllSelect" type="checkbox" />
      </template>
    </v-client-table>
    
  </div>
</template>

<script>
  import { rootUrl } from '../../app.config.js'

  export default {
      name: 'CallMgmtApp',
      components: {},
      data() {
        return {
          selected: false,
          columns: ['selected', 'id', 'Nurse','Patient','Status', 'Practice', 'Last Call Status', 'Next Call', 'Call Time Start', 'Call Time End', 'Time Zone', 'Preferred Call Days', 'Last Call', 'CCM Time'],
          tableData: [],
          options: {
          // see the options API
            columnsClasses: {
              'selected': 'blank'
            },
            sortable: ['id', 'Nurse','Patient','Status', 'Practice', 'Last Call Status', 'Next Call', 'Call Time Start', 'Call Time End', 'Time Zone', 'Preferred Call Days', 'Last Call', 'CCM Time']
          }
        }
      },
      methods: {
        toggleAllSelect(e) {
          this.tableData = this.tableData.map(row => {
            row.selected = this.selected;
            return row;
          })
        }
      },
      mounted() {
        this.$http.get(rootUrl('api/admin/calls')).then((result) => result.data).then(result => {
          const calls = result.data;
          calls.forEach(call => {
            call.getNurse = () => ((call.inbound_user && call.inbound_user.nurse_info) ?
                                            call.inbound_user : 
                                      (call.outbound_user && call.outbound_user.nurse_info) ?
                                            call.outbound_user : 
                                            null)
            call.getPatient = () => ((call.inbound_user && call.inbound_user.patient_info) ?
                                            call.inbound_user : 
                                      (call.outbound_user && call.outbound_user.patient_info) ?
                                            call.outbound_user : 
                                            null)
          })
          const tableCalls = calls.map(call => ({
                                id: call.id,
                                selected: false,
                                Nurse: (call.getNurse() || {}).full_name,
                                Patient: (call.getPatient() || {}).full_name,
                                Status: call.status,
                                Practice: (call.getNurse() || {}).primary_practice_id,
                                'Next Call': call.scheduled_date,
                                'Call Time Start': call.window_start,
                                'Call Time End': call.window_end
                              }))
          this.tableData = this.tableData.concat(tableCalls)
          console.log(calls);
          console.log(this.$refs);
        })
      }
  }
</script>

<style>
  .VueTables__child-row-toggler {
    display: block;
    width: 20px;
    height: 20px;
    border: 1px solid #AAA;
    border-radius: 50%;
    cursor: pointer;
  }

  .VueTables__child-row-toggler.VueTables__child-row-toggler--open {
    border-color: #0AF;
  }

  .row-select {
    font-size: 20px;
  }
</style>