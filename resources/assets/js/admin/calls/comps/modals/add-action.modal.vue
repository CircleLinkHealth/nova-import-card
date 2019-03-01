<template>
    <modal name="add-action" :info="addActionModalInfo" :no-footer="true" class-name="modal-add-action">
        <template slot="title">
            <div class="row">
                <div class="col-sm-6">
                    Add New Activity(ies)
                </div>
            </div>
        </template>
        <template slot-scope="props">
            <form action="#" @submit="submitForm">

                <div class="row">
                    <table class="add-actions">
                        <thead>
                        <tr>
                            <th class="family-override">
                                <a v-show="hasToConfirmFamilyOverrides" class='my-tool-tip' data-toggle="tooltip"
                                   data-placement="top"
                                   title="Tick to confirm family call override">
                                    <i class='glyphicon glyphicon-info-sign'></i>
                                </a>
                            </th>
                            <th class="sub-type">
                                Type
                                <span class="required">*</span>
                            </th>
                            <th class="practices">
                                Practice
                                <loader v-show="loaders.practices"></loader>
                            </th>
                            <th class="patients">
                                Patient
                                <span class="required">*</span>
                                <a class='my-tool-tip' data-toggle="tooltip" data-placement="top"
                                   title="Tick to show only unscheduled">
                                    <i class='glyphicon glyphicon-info-sign'></i>
                                </a>
                                <loader v-show="loaders.patients"></loader>
                            </th>
                            <th class="nurses">
                                Nurse
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
                            <th>
                                <a class='my-tool-tip' data-toggle="tooltip" data-placement="top"
                                   title="Tick if patient requested call time">
                                    <i class='glyphicon glyphicon-info-sign'></i>
                                </a>
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
                                <div v-show="action.showFamilyOverride">
                                    <input type="checkbox" id="family_override"
                                           name="family_override" v-model="action.data.familyOverride"
                                           :disabled="action.disabled"/>
                                </div>
                            </td>
                            <td>
                                <v-select :disabled="action.disabled"
                                          max-height="200px" class="form-control" v-model="action.selectedSubTypeData"
                                          :options="subTypesForSelect"
                                          @input="function (type) {changeSubType(index, type)}" required>
                                </v-select>
                            </td>
                            <td>
                                <v-select :disabled="action.disabled"
                                          max-height="200px" class="form-control" v-model="action.selectedPracticeData"
                                          :options="practicesForSelect"
                                          @input="function (practice) {changePractice(index, practice)}">
                                </v-select>
                            </td>
                            <td>
                                <div class="width-90">
                                    <v-select :disabled="action.disabled"
                                              max-height="200px" class="form-control" name="inbound_cpm_id"
                                              v-model="action.selectedPatientData"
                                              :options="action.patientsForSelect"
                                              @input="function (patient) {changePatient(index, patient)}"
                                              required>
                                    </v-select>
                                </div>
                                <div class="width-10 padding-left-5 padding-top-7">
                                    <input :disabled="action.disabled"
                                           type="checkbox" v-model="action.filters.showUnscheduledPatients"
                                           @change="function (e) { changeUnscheduledPatients(index, e); }"/>
                                </div>

                            </td>
                            <td>
                                <v-select :disabled="action.disabled"
                                          max-height="200px"
                                          class="form-control" name="outbound_cpm_id"
                                          v-model="action.selectedNurseData"
                                          :options="action.nursesForSelect"
                                          @input="function (nurse) {changeNurse(index, nurse)}"
                                          required>
                                </v-select>
                            </td>
                            <td>
                                <input class="form-control" type="date" name="scheduled_date" v-model="action.data.date"
                                       :disabled="action.disabled" required/>
                            </td>
                            <td>
                                <input class="form-control" type="time" name="window_start"
                                       v-model="action.data.startTime"
                                       :disabled="action.disabled" required/>
                            </td>
                            <td>
                                <div class="width-83">
                                    <input class="form-control" type="time" name="window_end"
                                           v-model="action.data.endTime"
                                           :disabled="action.disabled" required/>
                                </div>
                            </td>
                            <td>
                                <div class="width-17 padding-left-5 padding-top-7">
                                    <input type="checkbox" id="is_manual"
                                           name="is_manual" v-model="action.data.isManual"
                                           :disabled="action.disabled"/>
                                </div>
                            </td>
                            <td>
                                <input class="form-control" type="text" name="text" v-model="action.data.text"
                                       :disabled="action.disabled"/>
                            </td>
                            <td>
                                       <span class="btn btn-xs" @click="removeAction(index)"
                                             v-show="actions.length > 1">
                                           <i class="glyphicon glyphicon-remove"></i>
                                       </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <div class="alert alert-danger" v-if="hasNotAvailableNurses">
                        No available nurses for selected patient
                    </div>
                    <div class="alert alert-warning" v-if="hasPatientInDraftMode">
                        Action not allowed: Care plan is in draft mode. QA the care plan first.
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
    </modal>
</template>

<script>
    import {Event} from 'vue-tables-2'
    import Modal from '../../../common/modal'
    import LoaderComponent from '../../../../components/loader'
    import {rootUrl} from '../../../../app.config'
    import {today} from '../../../../util/today'
    import notifications from '../../../../components/notifications'
    import VueSelect from 'vue-select'
    import VueCache from '../../../../util/vue-cache'

    const UNASSIGNED_VALUE = {label: 'Unassigned', value: null};
    const CALL_MUST_OVERRIDE_STATUS_CODE = 418;
    const CALL_MUST_OVERRIDE_WARNING = "The family members of this patient have a call scheduled at different time. Please confirm you still want to schedule this call by checking the box.";

    const defaultFormData = {
        type: null,
        subType: null,
        practiceId: null,
        patientId: null,
        nurseId: null,
        date: today(),
        startTime: '09:00',
        endTime: '17:00',
        text: null,
        isManual: 0,
        familyOverride: 0
    };

    function getNewAction() {
        return {
            disabled: false,
            showFamilyOverride: false,
            data: Object.assign({}, defaultFormData),
            filters: {
                showUnscheduledPatients: false
            },
            patients: [],
            nurses: [],
            selectedSubTypeData: UNASSIGNED_VALUE,
            selectedPatientData: UNASSIGNED_VALUE,
            selectedPracticeData: UNASSIGNED_VALUE,
            selectedNurseData: UNASSIGNED_VALUE,
            selectedPatientIsInDraftMode: false,
            nursesForSelect: [],
            patientsForSelect: [],

        };
    }

    //need in cancelHandler.
    let self;

    export default {
        name: 'add-action-modal',
        mixins: [VueCache],
        components: {
            'modal': Modal,
            'loader': LoaderComponent,
            'v-select': VueSelect,
            notifications
        },
        data() {
            return {
                addActionModalInfo: {
                    okHandler() {
                        const form = this.$form();
                        this.errors().submit = null;
                        console.log("form:add-action:submit", form);
                        form.querySelector('button.submit.hidden').click();
                    },
                    cancelHandler() {
                        self.resetForm();
                        this.errors().submit = null;
                        Event.$emit("modal-add-action:hide")
                    },
                    $form: () => this.$el.querySelector('form'),
                    errors: () => this.errors
                },
                errors: {
                    practices: null,
                    patients: null,
                    submit: null
                },
                loaders: {
                    practices: false,
                    patients: false,
                    submit: false,
                    nurses: false
                },

                subTypesForSelect: [
                    {label: 'Call', value: 'call'},
                    {label: 'Call back', value: 'Call Back'},
                    {label: 'Refill', value: 'Refill'},
                    {label: 'Send Info', value: 'Send Info'},
                    {label: 'Get appt.', value: 'Get Appt.'},
                    {label: 'CP Review', value: 'CP Review'},
                    {label: 'Other Task', value: 'Other Task'}
                ],

                practices: [],
                practicesForSelect: [],

                actions: [getNewAction()]
            }
        },
        computed: {
            hasNotAvailableNurses() {
                return this.actions.filter(x => x.data.practiceId && x.nursesForSelect.length === 0).length > 0;
            },
            hasPatientInDraftMode() {
                return this.actions.filter(x => x.selectedPatientIsInDraftMode).length > 0;
            },
            hasToConfirmFamilyOverrides() {
                return this.actions.filter(x => x.showFamilyOverride).length > 0;
            }
        },
        methods: {
            setNursesForSelect(actionIndex) {
                this.actions[actionIndex].nursesForSelect = [
                    UNASSIGNED_VALUE,
                    ...this.actions[actionIndex].nurses.map(nurse => ({
                        label: nurse.full_name,
                        value: nurse.id
                    }))]
            },
            setPracticesForSelect() {
                this.practicesForSelect = [
                    UNASSIGNED_VALUE,
                    ...this.practices.map(practice => ({
                        label: practice.display_name,
                        value: practice.id
                    }))]
            },
            setPatientsForSelect(actionIndex) {
                this.actions[actionIndex].patientsForSelect = [
                    UNASSIGNED_VALUE,
                    ...this.actions[actionIndex].patients.map(patient => ({
                        label: patient.name + ' (' + patient.id + ')',
                        value: patient.id
                    }))]
            },

            resetForm() {
                this.actions.splice(0);
                this.addNewAction();
            },

            addNewAction() {
                this.actions.push(getNewAction());
            },
            removeAction(id) {
                this.$delete(this.actions, id);
            },
            selectedPatient(actionIndex) {
                return (this.actions[actionIndex].patients.find(patient => patient.id === this.actions[actionIndex].data.patientId) || {});
            },
            setPractice(actionIndex, practiceId) {
                if (practiceId) {
                    this.actions[actionIndex].data.practiceId = practiceId;
                    const practice = this.practices.find(practice => practice.id === this.actions[actionIndex].data.practiceId);
                    if (practice) {
                        if (!this.actions[actionIndex].selectedPracticeData || this.actions[actionIndex].selectedPracticeData.value !== practice.id) {
                            this.actions[actionIndex].selectedPracticeData = {
                                label: practice.display_name,
                                value: practice.id
                            }
                        }
                    }
                    else {
                        this.actions[actionIndex].selectedPracticeData = UNASSIGNED_VALUE;
                    }
                }
            },
            changeSubType(actionIndex, type) {
                if (type) {
                    if (type.value === "call") {
                        this.actions[actionIndex].data.type = 'call';
                        this.actions[actionIndex].data.subType = null;
                    }
                    else {
                        this.actions[actionIndex].data.type = 'task';
                        this.actions[actionIndex].data.subType = type.value;
                    }

                }
            },
            changePatient(actionIndex, patient) {
                if (patient) {
                    this.actions[actionIndex].data.patientId = patient.value;
                    const selectedPatient = this.selectedPatient(actionIndex);
                    this.setPractice(actionIndex, selectedPatient.program_id);
                    this.actions[actionIndex].selectedPatientIsInDraftMode = (selectedPatient.status === 'draft');
                }
            },
            changePractice(actionIndex, practice) {
                if (practice) {

                    //reset selected patient only if practice is different
                    if (this.actions[actionIndex].data.practiceId !== practice.value) {
                        this.selectedPatientData = UNASSIGNED_VALUE;
                    }
                    //always reset selected nurse
                    this.actions[actionIndex].selectedNurseData = UNASSIGNED_VALUE;

                    this.actions[actionIndex].data.practiceId = practice.value;
                    return Promise.all([this.getPatients(actionIndex), this.getNurses(actionIndex)])
                }
                return Promise.resolve([])
            },
            changeNurse(actionIndex, nurse) {
                if (nurse) {
                    this.actions[actionIndex].data.nurseId = nurse.value;
                }
            },
            changeUnscheduledPatients(actionIndex, e) {
                if (e && e.target) {
                    return e.target.checked ? this.getUnscheduledPatients(actionIndex) : this.getPatients(actionIndex)
                }
                return Promise.resolve([])
            },
            getPractices(actionIndex) {
                this.loaders.practices = true;
                return this.cache()
                    .get(rootUrl(`api/practices`))
                    .then(response => {
                        this.loaders.practices = false;
                        console.log('add-action:practices', response);
                        this.practices = (response || [])
                            .sort((a, b) => {
                                if (a.display_name < b.display_name) return -1;
                                else if (a.display_name > b.display_name) return 1;
                                else return 0
                            })
                            .distinct(patient => patient.id);
                        this.setPracticesForSelect();
                    }).catch(err => {
                        this.loaders.practices = false;
                        this.errors.practices = err.message;
                        console.error('add-action:practices', err)
                    })
            },
            getPatients(actionIndex) {
                return !this.actions[actionIndex].data.practiceId ?
                    this.getAllPatients(actionIndex) :
                    (this.actions[actionIndex].filters.showUnscheduledPatients ?
                            this.getUnscheduledPatients(actionIndex) :
                            this.getPracticePatients(actionIndex)
                    );
            },
            getUnscheduledPatients(actionIndex) {
                this.loaders.patients = true
                const practice_addendum = this.actions[actionIndex].data.practiceId ? `practices/${this.actions[actionIndex].data.practiceId}/` : '';
                return this.axios.get(rootUrl(`api/${practice_addendum}patients/without-scheduled-calls`)).then(response => {
                    this.loaders.patients = false;
                    const pagination = response.data;
                    console.log('add-action:patients:unscheduled', pagination);
                    return pagination;
                }).then((pagination) => {
                    this.actions[actionIndex].patients = ((pagination || {}).data || [])
                        .map(patient => {
                            patient.name = patient.full_name;
                            return patient;
                        })
                        .sort((a, b) => a.name > b.name ? 1 : -1)
                        .distinct(patient => patient.id);
                    this.setPatientsForSelect(actionIndex);
                }).catch(err => {
                    this.loaders.patients = false;
                    this.errors.patients = err.message;
                    console.error('add-action:patients:unscheduled', err)
                })
            },
            getPracticePatients(actionIndex) {
                if (this.actions[actionIndex].data.practiceId) {
                    this.loaders.patients = true;
                    return this.axios.get(rootUrl(`api/practices/${this.actions[actionIndex].data.practiceId}/patients`))
                        .then(response => {
                            this.loaders.patients = false;
                            console.log('add-action:patients:practice', response.data);
                            return response.data
                        }).then((patients = []) => {
                            this.actions[actionIndex].patients = patients
                                .map(patient => {
                                    patient.name = patient.full_name;
                                    return patient;
                                })
                                .sort((a, b) => a.name > b.name ? 1 : -1)
                                .distinct(patient => patient.id);
                            this.setPatientsForSelect(actionIndex);
                        }).catch(err => {
                            this.loaders.patients = false;
                            this.errors.patients = err.message;
                            console.error('add-action:patients:practice', err)
                        })
                }
                return Promise.resolve([])
            },
            getAllPatients() {
                //this is wrong.
                //need to revise
                /*
                this.loaders.patients = true
                return this.cache()
                    .get(rootUrl(`api/patients?rows=all&autocomplete`))
                    .then(response => {
                        this.loaders.patients = false
                        console.log('add-action:patients:all', response.data)
                        return response.data;
                    })
                    .then((patients = []) => {
                        return this.patients = patients.sort((a, b) => {
                            return a.name > b.name ? 1 : -1;
                        }).distinct(patient => patient.id)
                    }).catch(err => {
                        this.loaders.patients = false
                        this.errors.patients = err.message
                        console.error('add-action:patients:all', err)
                    })
                    */
            },
            getNurses(actionIndex) {
                if (this.actions[actionIndex].data.practiceId) {
                    this.loaders.nurses = true;
                    return this.axios.get(rootUrl(`api/practices/${this.actions[actionIndex].data.practiceId}/nurses`))
                        .then(response => {
                            this.loaders.nurses = false;
                            this.actions[actionIndex].nurses = (response.data || []).map(nurse => {
                                nurse.name = nurse.full_name;
                                return nurse;
                            }).filter(nurse => nurse.name && nurse.name.trim() !== '');
                            console.log('add-action-get-nurses', this.actions[actionIndex].nurses);
                            this.setNursesForSelect(actionIndex);
                            return this.actions[actionIndex].nurses;
                        }).catch(err => {
                            console.error('add-action-get-nurses', err);
                            this.loaders.nurses = false;
                            this.errors.nurses = err.message
                        })
                }
                return Promise.resolve([])
            },
            submitForm(e) {
                e.preventDefault();

                Event.$emit('notifications-add-action-modal:dismissAll');

                const patientIds = [];
                const patients = [];
                const formData = this.actions
                    .filter(action => {
                        //filter out any actions that are not filled out

                        if (action.disabled) {
                            return false;
                        }

                        const data = action.data;
                        //CPM-291 - allow unassigned nurse
                        //if (call.patientId === null || call.nurseId === null || call.practiceId === null) {
                        if (data.patientId === null || data.practiceId === null) {
                            return false;
                        }
                        return true;
                    })
                    .map(action => {
                        const data = action.data;
                        patients.push(action.patients.find(patient => patient.id === data.patientId));
                        patientIds.push(data.patientId);
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
                            family_override: data.familyOverride
                        };
                    });

                if (!patients.length) {
                    Event.$emit('notifications-add-action-modal:create', {
                        text: `Patient not found`,
                        type: 'warning'
                    });
                    return;
                }

                //if any patient has status draft, we do not allow creation
                const draftPatients = patients.filter(x => x.status === 'draft');
                if (draftPatients.length) {
                    Event.$emit('notifications-add-action-modal:create', {
                        text: `Action not allowed: This patients' [${draftPatients.map(x => x.name || x.full_name).join(', ')}] care plan is in draft mode. QA the care plan before scheduling a call`,
                        type: 'warning'
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
                                        }
                                        else if (Array.isArray(action.errors)) {
                                            msg += action.errors.join(', ');
                                        }
                                        else {
                                            const errorsMessages = Object.values(action.errors).map(x => x[0]).join(', ');
                                            msg += errorsMessages;
                                        }

                                        Event.$emit('notifications-add-action-modal:create', {
                                            text: msg,
                                            type: 'error',
                                            noTimeout: true
                                        });
                                    }
                                    else {
                                        Event.$emit('notifications-add-action-modal:create', {
                                            text: `Action[${index + 1}]: Created successfully`,
                                            noTimeout: true
                                        });
                                        Event.$emit('actions:add', call);
                                    }

                                });

                            }
                            else {
                                this.resetForm();
                                Event.$emit("modal-add-action:hide");
                                actions.forEach(action => {
                                    Event.$emit('actions:add', action);
                                });

                                console.log('actions:add', actions);
                            }

                        }
                        else {
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
                                }
                                else {
                                    const errorsMessages = Object.values(errors).map(x => x[0]).join(', ');
                                    msg += `: ${errorsMessages}`;
                                }
                            }
                            else if (err.response.data.message) {
                                msg += `: ${err.response.data.message}`;
                            }

                        }

                        Event.$emit('notifications-add-action-modal:create', {text: msg, type: 'error'})
                    });
            }
        },
        created() {
            return Promise.all([this.getPractices(0), this.getPatients(0)])
        },
        mounted() {

            self = this;

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

            Event.$on('modal-add-action:show', () => {

                const el = '.modal-container';
                waitForEl(el, () => {
                    $(el).css('width', document.body.offsetWidth * 0.95);
                });

                const el2 = "a.my-tool-tip";
                waitForEl(el2, () => {
                    //initialize tooltips
                    $(el2).tooltip();
                });

            });

            Event.$on('add-action-modals:set', (data) => {
                if (data) {
                    if (data.practiceId) {
                        // this.setPractice(data.practiceId)
                    }
                    if (data.patientId) {
                        console.log(data);
                        this.actions[this.actions.length - 1].data.patientId = data.patientId;
                        this.actions[this.actions.length - 1].selectedPatientData = {
                            label: data.patientName,
                            value: data.patientId
                        };
                        this.actions[this.actions.length - 1].selectedPatientData.label = data.patientName
                        this.actions[this.actions.length - 1].selectedPatientData.value = data.patientId
                    }
                }
            })
        }
    }
</script>

<style>

    .modal-add-action .modal-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        margin-top: 40px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    /*width will be set automatically when modal is mounted*/
    .modal-add-action .modal-container {
        width: 1200px;
    }

    .modal-add-action th, .modal-add-action td {
        padding: 0.4em 0.4em;
        position: relative;
    }

    .modal-add-action table.add-actions {
        table-layout: fixed;
    }

    .modal-add-action table.add-actions th.sub-type {
        width: 5%;
    }

    .modal-add-action table.add-actions th.practices {
        width: 16%;
    }

    .modal-add-action table.add-actions th.patients {
        width: 16%;
    }

    .modal-add-action table.add-actions th.nurses {
        width: 15%;
    }

    .modal-add-action table.add-actions th.date {
        width: 10%;
    }

    .modal-add-action table.add-actions th.start-time {
        width: 8%;
    }

    .modal-add-action table.add-actions th.end-time {
        width: 11%;
    }

    .modal-add-action table.add-actions th.notes {
        width: 16%;
    }

    .modal-add-action table.add-actions th.remove {
        width: 3%;
    }

    .modal-add-call table.add-calls th.family-override {
        width: 2%;
    }

    .modal-add-action .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .modal-add-action .glyphicon-remove {
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
        overflow: initial;
    }

    .v-select .dropdown-toggle .clear {

    }

    .modal-add-action .modal-body {
        min-height: 300px;
    }

    .selected-tag {
        width: 80%;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    a.my-tool-tip {
        float: right;
        margin-right: 4px;
    }


</style>
