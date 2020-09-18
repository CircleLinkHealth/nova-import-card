<template>
    <modal v-show="show" class="modal-add-task">
        <template slot="header">
            <button type="button" class="close" @click="clearOpenModal">×</button>
            <h4 class="modal-title">Add Activity(ies)</h4>
        </template>
        <template slot="body">
            <form id="add-task-form" action="#" @submit="submitForm">

                <div class="row">
                    <table class="add-actions">
                        <thead>
                        <tr>

                            <th class="sub-type">
                                Type
                                <span class="required">*</span>
                            </th>

                            <th class="nurses">
                                Care Coach
                                <loader v-show="loaders.nurses"></loader>
                            </th>
                            <th class="date">
                                Date
                                <span class="required">*</span>
                            </th>
                            <th class="start-time">
                                Start Time <span class="required">*</span>
                            </th>
                            <th class="end-time">
                                End Time
                                <span class="required">*</span>
                            </th>
                            <th class="notes">
                                Activity Note
                            </th>
                            <th class="remove">
                                &nbsp;
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(action, index) in actions">

                            <td>
                                <v-select :disabled="action.disabled"
                                          max-height="200px" class="form-control" v-model="action.selectedSubTypeData"
                                          :options="subTypesForSelect"
                                          @input="function (type) {changeSubType(index, type)}">
                                </v-select>
                            </td>

                            <td>
                                <v-select :disabled="action.disabled"
                                          max-height="200px"
                                          class="form-control" name="outbound_cpm_id"
                                          v-model="action.selectedNurseData"
                                          :options="nursesForSelect"
                                          @input="function (nurse) {changeNurse(index, nurse)}"
                                          required>
                                </v-select>
                            </td>
                            <td>
                                <span class="asap_label" style="font-weight: bold">ASAP</span>
                                <a data-toggle="tooltip" data-placement="top"
                                   style="color: #000;"
                                   title="Tick to schedule as:'As soon as possible'">
                                    <input v-model="action.data.asapChecked"
                                           id="asap"
                                           type="checkbox"
                                           name="asap_check"
                                           style="float: right; margin-top: -18px; display: block;"
                                           :disabled="action.disabled">
                                </a>

                                <input class="form-control height-40" type="date" name="scheduled_date" v-model="action.data.date"
                                       :disabled="action.disabled" required/>
                            </td>
                            <td>
                                <input class="form-control" type="time" name="window_start"
                                       v-model="action.data.startTime"
                                       :disabled="action.data.asapChecked || action.disabled" required/>
                            </td>
                            <td>
                                <input class="form-control" type="time" name="window_end"
                                       v-model="action.data.endTime"
                                       :disabled="action.data.asapChecked || action.disabled" required/>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="text" v-model="action.data.text"
                                       :disabled="action.disabled"/>
                            </td>
                            <td>
                                <span class="btn btn-xs" @click="removeAction(index)" v-show="actions.length > 1">
                                    <i class="glyphicon glyphicon-remove"></i>
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="alert alert-danger" v-if="hasNotAvailableNurses">
                        No available nurses for selected patient
                    </div>
                </div>

                <br/>
                <div class="row">
                    <div class="btn btn-primary btn-xs" @click="addNewAction">
                        Add New Activity
                    </div>
                </div>

                <br/>

                <div class="row">
                    <div class="col-sm-12">
                        <notifications ref="notificationsComponent" name="add-action-modal"></notifications>
                        <center>
                            <loader v-if="loaders.submit"></loader>
                        </center>
                    </div>
                </div>
                <button class="submit hidden"></button>
            </form>
        </template>
        <template slot="footer">
            <div class="row">
                <div class="col-md-12 text-right">
                    <button class="btn btn-danger" @click="clearOpenModal">Cancel</button>

                    <button :disabled="false" @click="buttonSave" class=" btn btn-info">
                        Save <i v-if="false" class="fa fa-spinner fa-pulse fa-fw"></i>
                    </button>
                </div>

            </div>
        </template>
    </modal>
</template>

<script>

    /**
     * NOTE: this modal is a lite version of the add-action.modal of Patient Activity Management
     */

    import {Event} from 'vue-tables-2'
    import Modal from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/shared/modal';
    import LoaderComponent from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/loader';
    import {rootUrl} from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config';
    import {today} from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/util/today';
    import notifications from '../../notifications';
    import VueSelect from 'vue-select';
    import VueCache from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/util/vue-cache';
    import {mapActions} from 'vuex';

    const UNASSIGNED_VALUE = {label: 'Unassigned', value: null};

    //need in cancelHandler.
    let self;

    export default {
        name: 'add-task-modal',
        mixins: [VueCache],
        components: {
            'modal': Modal,
            'loader': LoaderComponent,
            'v-select': VueSelect,
            notifications
        },
        props: {
            show: {
                type: Boolean,
                default: false
            },
            practice: {
                type: Object,
                default: false
            },
            patientId: {
                type: Number,
                default: false
            }
        },
        data() {
            return {
                asapChecked: 0,
                errors: {
                    submit: null
                },
                loaders: {
                    submit: false,
                    nurses: false
                },

                subTypesForSelect: [
                    {label: 'Call back', value: 'Call Back'},
                    {label: 'Refill', value: 'Refill'},
                    {label: 'Send Info', value: 'Send Info'},
                    {label: 'Get appt.', value: 'Get Appt.'},
                    {label: 'CP Review', value: 'CP Review'},
                    {label: 'Other Task', value: 'Other Task'}
                ],

                nurses: [],
                nursesForSelect: [],

                actions: []
            }
        },
        computed: {
            hasNotAvailableNurses() {
                return this.nursesForSelect.length === 0 && !this.loaders.nurses;
            },
            hasToConfirmFamilyOverrides() {
                return this.actions.filter(x => x.showFamilyOverride).length > 0;
            }
        },
        methods: Object.assign({},
            mapActions(['clearOpenModal']),
            {
                getDefaultFormData() {
                    return {
                        type: 'task',
                        subType: null,
                        practiceId: this.practice.id,
                        patientId: this.patientId,
                        nurseId: null,
                        date: today(),
                        startTime: '09:00',
                        endTime: '17:00',
                        text: null,
                        isManual: 1,
                        familyOverride: 0
                    }
                },

                getNewAction() {
                    return {
                        disabled: false,
                        showFamilyOverride: false,
                        data: Object.assign({}, this.getDefaultFormData()),
                        selectedSubTypeData: UNASSIGNED_VALUE,
                        selectedNurseData: UNASSIGNED_VALUE,
                    };
                },

                setNursesForSelect() {
                    this.nursesForSelect = [
                        UNASSIGNED_VALUE,
                        ...this.nurses.map(nurse => ({
                            label: nurse.full_name,
                            value: nurse.id
                        }))]
                },

                resetForm() {
                    this.actions.splice(0);
                    this.addNewAction();
                },

                addNewAction() {
                    this.actions.push(this.getNewAction());
                },
                removeAction(id) {
                    this.$delete(this.actions, id);
                },
                changeSubType(actionIndex, type) {
                    if (type) {
                        if (type.value === "call") {
                            this.actions[actionIndex].data.type = 'call';
                            this.actions[actionIndex].data.subType = null;
                        } else {
                            this.actions[actionIndex].data.type = 'task';
                            this.actions[actionIndex].data.subType = type.value;
                        }

                    }
                },

                changeNurse(actionIndex, nurse) {
                    if (nurse) {
                        this.actions[actionIndex].data.nurseId = nurse.value;
                    }
                },


                getNurses() {
                    this.loaders.nurses = true;
                    return this.axios
                        .get(rootUrl(`api/practices/${this.practice.id}/nurses`))
                        .then(response => {
                            this.loaders.nurses = false;
                            this.nurses = (response.data || [])
                                .map(nurse => {
                                    nurse.name = nurse.full_name;
                                    return nurse;
                                })
                                .filter(nurse => nurse.name && nurse.name.trim() !== '');
                            this.setNursesForSelect();
                            return this.nurses;
                        })
                        .catch(err => {
                            this.loaders.nurses = false;
                            this.errors.nurses = err.message
                        });
                },
                buttonSave() {
                    const form = document.getElementById("add-task-form");
                    form.querySelector('button.submit.hidden').click();
                },
                submitForm(e) {
                    e.preventDefault();

                    Event.$emit('notifications-add-action-modal:dismissAll');

                    const formData = this.actions
                        .filter(action => {
                            //filter out any actions that are not filled out

                            if (action.disabled) {
                                return false;
                            }

                            const data = action.data;
                            //CPM-291 - allow unassigned nurse
                            //if (call.patientId === null || call.nurseId === null || call.practiceId === null) {
                            return !(data.subType == null || data.patientId === null || data.practiceId === null);

                        })
                        .map(action => {
                            const data = action.data;
                            return {
                                type: data.type,
                                sub_type: data.subType,
                                inbound_cpm_id: data.patientId,
                                outbound_cpm_id: data.nurseId,
                                scheduled_date: data.date,
                                window_start: data.startTime,
                                window_end: data.endTime,
                                attempt_note: data.text,
                                is_manual: data.isManual,
                                asap: data.asapChecked,
                                family_override: data.familyOverride
                            };
                        });

                    if (formData.length === 0) {
                        Event.$emit('notifications-add-action-modal:create', {
                            text: "Please make sure all required fields are set.",
                            type: 'error',
                            noTimeout: true
                        });
                        return;
                    }

                    this.actions.forEach(x => {
                        x.disabled = true;
                    });

                    this.loaders.submit = true;
                    return this.axios.post(rootUrl('callcreate-multi'), formData)
                        .then((response, status) => {
                            if (response) {
                                this.loaders.submit = false;

                                //we have to check that all calls have been placed successfully
                                const actions = response.data || [];
                                const errors = actions.filter(x => x.errors && x.errors.length > 0);
                                if (errors.length) {

                                    //we have to go through all calls and display if call was saved or not
                                    actions.forEach((action, index) => {

                                        if (action.errors && action.errors.length > 0) {

                                            //enable this action so it can be edited
                                            this.actions[index].disabled = false;

                                            const familyOverrideError = action.code === CALL_MUST_OVERRIDE_STATUS_CODE;
                                            this.actions[index].showFamilyOverride = familyOverrideError;

                                            let msg = `Action[${index + 1}]: `;

                                            if (familyOverrideError) {
                                                msg += CALL_MUST_OVERRIDE_WARNING;
                                            } else if (Array.isArray(action.errors)) {
                                                msg += action.errors.join(', ');
                                            } else {
                                                const errorsMessages = Object.values(action.errors).map(x => x[0]).join(', ');
                                                msg += errorsMessages;
                                            }

                                            Event.$emit('notifications-add-action-modal:create', {
                                                text: msg,
                                                type: 'error',
                                                noTimeout: true
                                            });
                                        } else {
                                            Event.$emit('notifications-add-action-modal:create', {
                                                text: `Action[${index + 1}]: Created successfully`,
                                                noTimeout: true
                                            });
                                            Event.$emit('actions:add', call);
                                        }

                                    });

                                } else {
                                    this.clearOpenModal();
                                }

                            } else {
                                throw new Error('Could not create call. Patient already has a scheduled call')
                            }
                        }).catch(err => {

                            //we assume this is a generic error
                            //eg. error 500, 504
                            this.actions.forEach(x => {
                                x.disabled = false;
                            });

                            this.errors.submit = err.message
                            this.loaders.submit = false
                            console.error('add-action', err)

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

                            Event.$emit('notifications-add-action-modal:create', {text: msg, type: 'error'})
                        });
                }
            }),
        created() {
            this.getNurses();
        },
        mounted() {

            self = this;

            this.addNewAction();

            const waitForEl = function (selector, callback) {
                if (!$(selector).length) {
                    setTimeout(function () {
                        window.requestAnimationFrame(function () {
                            waitForEl(selector, callback)
                        });
                    }, 100);
                } else {
                    callback();
                }
            };

            if (this.show) {
                const el = '.vue-modal-container';
                waitForEl(el, () => {
                    $(el).css('width', document.body.offsetWidth * 0.95);
                });

                const el2 = "a.my-tool-tip";
                waitForEl(el2, () => {
                    //initialize tooltips
                    $(el2).tooltip();
                });
            }
        }
    }
</script>

<style>

    .modal-add-task .vue-modal-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        margin-top: 40px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    .modal-add-task .vue-modal-container {
        /*width will be set automatically when modal is mounted*/
        width: 1200px;
    }

    .modal-add-task th, .modal-add-task td {
        padding: 0.4em 0.4em;
        position: relative;
    }

    .modal-add-task table.add-actions {
        table-layout: fixed;
    }

    .modal-add-task table.add-actions th.sub-type {
        width: 10%;
    }

    .modal-add-task table.add-actions th.nurses {
        width: 15%;
    }

    .modal-add-task table.add-actions th.date {
        width: 12%;
    }

    .modal-add-task table.add-actions th.start-time {
        width: 12%;
    }

    .modal-add-task table.add-actions th.end-time {
        width: 12%;
    }

    .modal-add-task table.add-actions th.notes {
        width: 22%;
    }

    .modal-add-task table.add-actions th.remove {
        width: 5%;
    }

    .modal-add-task .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .modal-add-task .glyphicon-remove {
        width: 20px;
        height: 20px;
        color: #d44a4a;
        vertical-align: middle;
        font-size: 20px;
    }

    .width-90 {
        float: left;
        width: 90%;
    }

    .width-82 {
        float: left;
        width: 82%;
    }

    .width-18 {
        float: left;
        width: 18%;
    }

    .width-10 {
        float: left;
        width: 10%;
    }

    .padding-left-5 {
        padding-left: 5px;
    }

    .padding-top-7 {
        padding-top: 7px;
    }

    span.required {
        color: red;
        font-size: 18px;
        position: absolute;
        top: 2px;
    }

    .show-only-unscheduled {
        margin-left: 10px;
    }

    .dropdown.v-select.form-control {
        height: auto;
        padding: 0;
    }

    .v-select .dropdown-toggle {
        height: 34px;
        overflow: hidden;
    }

    .modal-add-task .vue-modal-body {
        min-height: 300px;
    }

    .selected-tag {
        width: 80%;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }
    .asap_label {
        margin-left: 67%;
        position: absolute;
        margin-top: -10%;
    }
</style>