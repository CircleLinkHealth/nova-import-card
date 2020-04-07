<template>
    <modal name="select-times" :no-title="true" :no-footer="true" :info="selectTimesModalInfo"
           class-name="modal-select-times">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="text-center" v-if="!selectedPatients.length">
                        No patients selected
                    </div>
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <h4>Change Activity Date to:</h4>
                            <div class="row">
                                <div class="col-lg-6 col-sm-6 col-xs-4">
                                    <datepicker class="form-control"
                                                v-model="props.info.nextCall"></datepicker>
                                </div>
                                <div class="col-sm-4 hidden">
                                    <input class="form-control" type="time" v-model="props.info.callTimeStart"/>
                                </div>
                                <div class="col-sm-4 hidden">
                                    <input class="form-control" type="time" v-model="props.info.callTimeEnd"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <hr>
                            <div class="row">
                                <div class="col-lg-10 col-sm-4">
                                    <strong>
                                        Name
                                    </strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 patients-table">
                            <div class="row" v-for="call in calls" :key="call.patient.id">
                                <div class="col-sm-1">
                                    <input v-show="call.showFamilyOverride"
                                           type="checkbox" id="family_override"
                                           name="family_override" v-model="call.familyOverride"
                                           :disabled="call.disabled"/>
                                </div>
                                <div class="col-lg-12 col-sm-6">
                                    <h5>
                                        {{call.patient['Patient']}} [id:{{call.patient['Patient ID']}}]
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 top-20">
                    <notifications name="select-times"></notifications>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import Modal from '../../../common/modal'
    import {Event} from 'vue-tables-2'
    import Notifications from '../../../../components/notifications'
    import Loader from '../../../../components/loader'
    import Datepicker from 'vuejs-datepicker'
    import moment from 'moment'
    import * as callUpdateFunctions from '../../utils/call-update.fn';

    const CALL_TIME_START = '09:00'
    const CALL_TIME_END = '17:00'

    const CALL_MUST_OVERRIDE_STATUS_CODE = 418;
    const CALL_MUST_OVERRIDE_WARNING = "The family members of this patient have a call scheduled at different time. Please confirm you still want to schedule this call by ticking the checkbox.";

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
        data() {
            const $vm = this;
            return {
                today: moment().add(-1, 'days').toDate(),
                calls: [],
                selectTimesModalInfo: {
                    nextCall: moment(new Date()).format('YYYY-MM-DD'),
                    callTimeStart: CALL_TIME_START,
                    callTimeEnd: CALL_TIME_END,
                    okHandler() {

                        Event.$emit('notifications-select-times:dismissAll');

                        const nextCall = moment(this.nextCall).format('YYYY-MM-DD');
                        const callTimeStart = this.callTimeStart;
                        const callTimeEnd = this.callTimeEnd;

                        if (typeof ($vm.onChange) == 'function') {
                            $vm.onChange.call($vm, data)
                        }

                        const promises = [];
                        $vm.calls.forEach(item => {

                            if (item.disabled) {
                                return;
                            }

                            const call = item.patient;
                            const nonErrorPromise = new Promise((resolve, reject) => {
                                callUpdateFunctions.updateMultiValues(call, {
                                    nextCall,
                                    callTimeStart,
                                    callTimeEnd
                                }, item.familyOverride, call['Next Call'], () => {
                                })
                                    .then(x => {
                                        resolve({error: null, response: x});
                                    })
                                    .catch(err => {
                                        resolve({error: err});
                                    });
                            });
                            promises.push(nonErrorPromise);
                        });

                        $vm.calls.forEach(x => {
                            x.disabled = true;
                        });

                        Promise.all(promises)
                            .then(x => {
                                //if all successfull

                                let successIds = [];
                                let allSuccess = true;
                                x.forEach((prom, i) => {

                                    if (prom.error && prom.error.response.status === CALL_MUST_OVERRIDE_STATUS_CODE) {

                                        $vm.calls[i].disabled = false;
                                        $vm.calls[i].showFamilyOverride = true;

                                        allSuccess = false;

                                        Event.$emit('notifications-select-times:create', {
                                            text: `Call[${i + 1}]: ${CALL_MUST_OVERRIDE_WARNING}`,
                                            type: 'error',
                                            noTimeout: true
                                        });
                                    } else {
                                        successIds.push($vm.calls[i].patient.id);
                                        $vm.calls[i].disabled = true;
                                    }


                                });

                                if (allSuccess) {
                                    Event.$emit('modal-select-times:hide');
                                }

                                Event.$emit('select-times-modal:change', {callIDs: successIds, nextCall: nextCall});

                            })
                            .catch(err => {
                                //we assume this is a generic error
                                //eg. error 500, 504
                                $vm.calls.forEach(x => {
                                    x.disabled = false;
                                });

                                let msg = err.message;
                                if (err.response && err.response.data && err.response.data.errors) {

                                    if (err.response.data.errors) {
                                        // {is_manual: ['error message']}
                                        const errors = err.response.data.errors;
                                        if (Array.isArray(errors)) {
                                            msg += `: ${errors.join(', ')}`;
                                        } else {
                                            const errorsMessages = Object.values(errors).map(x => x[0]).join(', ');
                                            msg += `: ${errorsMessages}`;
                                        }
                                    } else if (err.response.data.message) {
                                        msg += `: ${err.response.data.message}`;
                                    }

                                }

                                Event.$emit('notifications-select-times:create', {text: msg, type: 'error'})
                            });

                    }
                },
                setCallsBasedOnProps() {
                    $vm.calls = $vm.selectedPatients.map(x => {
                        return {
                            disabled: false,
                            showFamilyOverride: false,
                            familyOverride: false,
                            patient: x
                        }
                    });
                }
            }
        },
        mounted() {
            Event.$on('modal-select-times:show', this.setCallsBasedOnProps);
        },
        beforeDestroy() {
            Event.$off('modal-select-times:show', this.setCallsBasedOnProps);
        }
    }
</script>

<style>

    .modal-select-times .modal-container {
        width: 500px;
    }

    .modal-select-times .patients-table {
        min-height: 300px;
    }

    .vdp-datepicker.form-control input[type='text'] {
        width: 100%;
    }
</style>