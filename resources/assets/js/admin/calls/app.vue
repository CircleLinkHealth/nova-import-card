<template>
  <div>
    <v-client-table :data="tableData" :columns="columns" :options="options"></v-client-table>
    <row-info :data="'hello'"></row-info>
  </div>
</template>

<script>
  import { rootUrl } from '../../app.config.js'
  import RowInfo from './comps/row-info'

  export default {
      name: 'CallMgmtApp',
      components: {
        'row-info': RowInfo
      },
      data() {
        return {
          columns: ['id', 'Nurse','Patient','Status', 'Practice', 'Last Call Status', 'Next Call', 'Call Time Start', 'Call Time End', 'Time Zone', 'Preferred Call Days', 'Last Call', 'CCM Time'],
          tableData: [],
          options: {
          // see the options API
            childRow: 'row-info'
          }
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
          console.log(this.tableData);
        })
      }
  }
</script>

<style>

</style>