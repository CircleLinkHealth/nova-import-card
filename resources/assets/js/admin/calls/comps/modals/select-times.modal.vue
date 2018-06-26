<template>
    <modal name="select-times" :no-title="true" :no-footer="true" :info="selectTimesModalInfo">
      <template slot-scope="props">
        <div class="row">
            <div class="col-sm-12">
                <div class="text-center" v-if="!selectedPatients.length">
                    No patients selected
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <datepicker class="form-control" :disabled="{ to: today }" v-model="props.info.nextCall"></datepicker>
                            </div>
                            <div class="col-sm-4 hidden">
                                <input class="form-control" type="time" v-model="props.info.callTimeStart" />
                            </div>
                            <div class="col-sm-4 hidden">
                                <input class="form-control" type="time" v-model="props.info.callTimeEnd" />
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                         <div class="row">
                            <div class="col-sm-4">
                                <strong>
                                    Name
                                </strong>
                            </div>
                            <div class="col-sm-4">
                                <strong>Next Call</strong>
                            </div>
                            <div class="col-sm-2">
                                <strong>Start</strong>
                            </div>
                            <div class="col-sm-2">
                                <strong>End</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12" v-for="patient in selectedPatients" :key="patient.id">
                        <div class="row">
                            <div class="col-sm-4">
                                <h5>
                                    {{patient.name}} [id:{{patient.id}}]
                                </h5>
                            </div>
                            <div class="col-sm-4">
                                {{patient.nextCall}}
                            </div>
                            <div class="col-sm-2">
                                {{patient.callTimeStart}}
                            </div>
                            <div class="col-sm-2">
                                {{patient.callTimeEnd}}
                                <loader v-if="patient.loaders.nextCall"></loader>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12 top-20">
                <notifications name="select-time"></notifications>
            </div>
        </div>
      </template>
    </modal>
</template>

<script>
    import Modal from '../../../common/modal'
    import { Event } from 'vue-tables-2'
    import { rootUrl } from '../../../../app.config'
    import Notifications from '../../../../components/notifications'
    import Loader from '../../../../components/loader'
    import Datepicker from 'vuejs-datepicker'
    import moment from 'moment'

    const CALL_TIME_START = '09:00'
    const CALL_TIME_END = '10:00'

    export default {
        name: 'select-times-modal',
        props: {
            selectedPatients: {
                type: Array,
                required: true
            },
            onChange: Function
        },
        components: {
            'modal': Modal,
            'notifications': Notifications,
            'loader': Loader,
            'datepicker': Datepicker
        },
        data () {
            const $vm = this;
            return {
                today: moment().add(-1, 'days').toDate(),
                selectTimesModalInfo: {
                    nextCall: moment(new Date()).format('YYYY-MM-DD'),
                    callTimeStart: '09:00',
                    callTimeEnd: '10:00',
                    okHandler () {
                        const data = { 
                            callIDs: $vm.selectedPatients.map(call => call.callId), 
                            nextCall: moment(this.nextCall).format('YYYY-MM-DD'), 
                            callTimeStart: this.callTimeStart, 
                            callTimeEnd: this.callTimeEnd 
                        }
                        if (typeof($vm.onChange) == 'function') {
                            $vm.onChange.call($vm, data)
                        }
                        Event.$emit('select-times-modal:change', data)
                        return data
                    }
                }
            }
        },
        mounted () {
            
        }
    }
</script>

<style>
    .vdp-datepicker.form-control input[type='text'] {
        width: 100%;
    }
</style>