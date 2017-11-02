<template>
  <div>
    <v-client-table ref="'tblCalls'" :data="tableData" :columns="columns" :options="options">
      <template slot="child_row" scope="props">
        <div class="row row-info">
          <div class="col-sm-12">
            <div class="row">
              <div class="col-lg-2">
                General Comment:
              </div>
              <div class="col-lg-10">
                <a href="#">
                    <span class="cpm-editable-icon" call-id="13687" column-name="general_comment" column-value="Call with spouse Luther Smith.">
                      Call with spouse Lorem Ipsum.
                    </span>
                  </a>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">Attempt Note:</div>
              <div class="col-lg-10">
                <a href="#"><span class="cpm-editable-icon" call-id="13687" column-name="attempt_note" column-value="Add Text">Add Text</span></a>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">Last 3 Notes:</div>
              <div class="col-lg-10">
                <ul>
                  <li>
                    Note 2016-11-21 13:06:00: 
                    <div class="label label-info" style="margin:5px;">Outbound Call</div>
                    <span style="font-weight:bold;">General (Clinical)</span> 
                    Attempted clinical check-in. Left voice message. 
                    </li>
                  <li>
                    Note 2016-11-18 11:52:00: 
                    <div class="label label-info" style="margin:5px;">Outbound Call</div>
                    <span style="font-weight:bold;">General (Clinical)</span> 
                    Attempted clinical check-in. Left voice message. 
                    </li>
                  <li>
                    Note 2016-11-11 12:35:00: 
                    <div class="label label-info" style="margin:5px;">Outbound Call</div>
                    <span style="font-weight:bold;">General (Clinical)</span> 
                    Attempted clinical check-in. Left voice message. 
                  </li>
                </ul>
              </div>
            </div>
            <div class="row">
              <div class="col-sm-2">Call Windows:</div>
              <div class="col-sm-10">
                <ul class="info-list">
                  <li>M: 09:00:00 - 17:00:00</li>
                  <li>Tu: 09:00:00 - 17:00:00</li>
                  <li>W: 09:00:00 - 17:00:00</li>
                  <li>Th: 09:00:00 - 17:00:00</li>
                  <li>F: 09:00:00 - 17:00:00</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
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

  .row-info ul{
    margin-left: -23px;
  }

  .row-info li {
    margin:5px 0px;
  }
</style>