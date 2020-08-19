<template>
    <modal name="add-action" :info="addActionModalInfo" :no-footer="true" class-name="modal-add-action">
        <template slot="title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Add Activity(ies)</h3>
                </div>
            </div>
        </template>
        <template slot-scope="props">
            <form action="#" @submit="submitForm">

                <div class="row">
                    <table class="add-actions">
                        <thead>
                        <tr>
                            <th class="sub-type" :class="showPracticeColumn ? 'with-practice-column' : ''">
                                Type
                                <span class="required">*</span>
                            </th>
                            <th class="practices" v-if="showPracticeColumn">
                                Practice
                                <span class="required">*</span>
                                <loader v-show="loaders.practices"></loader>
                            </th>
                            <th class="patients" :class="showPracticeColumn ? 'with-practice-column' : ''">
                                Patient
                                <span class="required">*</span>
                            </th>
                            <th class="patients-tool-tip">
                                <a class='my-tool-tip' data-toggle="tooltip" data-placement="top"
                                   title="Tick to show only unscheduled">
                                    <i class='glyphicon glyphicon-info-sign'></i>
                                </a>
                                <loader v-show="loaders.patients"></loader>
                            </th>
                            <th class="nurses" :class="showPracticeColumn ? 'with-practice-column' : ''">
                                Care Coach
                                <loader v-show="loaders.nurses"></loader>
                            </th>
                            <th class="date" :class="showPracticeColumn ? 'with-practice-column' : ''">
                                Date
                                <span class="required">*</span>
                            </th>
                            <th class="start-time" :class="showPracticeColumn ? 'with-practice-column' : ''">
                                Start Time <span class="required">*</span>
                            </th>
                            <th class="end-time" :class="showPracticeColumn ? 'with-practice-column' : ''">
                                End Time
                                <span class="required">*</span>
                            </th>
                            <th class="end-time-tooltip">
                                <a class='my-tool-tip' data-toggle="tooltip" data-placement="top"
                                   title="Tick if patient requested call time">
                                    <i class='glyphicon glyphicon-info-sign'></i>
                                </a>
                            </th>
                            <th class="notes" :class="showPracticeColumn ? 'with-practice-column' : ''">
                                Activity Note
                            </th>
                            <th class="family-override" :class="showPracticeColumn ? 'with-practice-column' : ''">
                                <a v-show="hasToConfirmFamilyOverrides" class='my-tool-tip' data-toggle="tooltip"
                                   data-placement="top"
                                   title="Tick to confirm family call override">
                                    <i class='glyphicon glyphicon-info-sign'></i>
                                </a>
                            </th>
                            <th class="remove" :class="showPracticeColumn ? 'with-practice-column' : ''">
                                &nbsp;
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(action, index) in actions">
                            <td>
                                <v-select :disabled="action.disabled"
                                          max-height="200px" class="form-control"
                                          v-model="action.selectedSubTypeData"
                                          :options="subTypesForSelect"
                                          @input="function (type) {changeSubType(index, type)}">
                                </v-select>
                            </td>
                            <td v-if="showPracticeColumn">
                                <v-select :disabled="action.disabled"
                                          max-height="200px" class="form-control"
                                          v-model="action.selectedPracticeData"
                                          :options="practicesForSelect"
                                          @input="function (practice) {changePractice(index, practice)}">
                                </v-select>
                            </td>
                            <td>
                                <v-select :disabled="action.disabled"
                                          max-height="200px" class="form-control"
                                          name="inbound_cpm_id"
                                          v-model="action.selectedPatientData"
                                          :options="action.patientsForSelect"
                                          @input="function (patient) {changePatient(index, patient)}" required>
                                </v-select>
                            </td>
                            <td>
                                <input :disabled="action.disabled"
                                       type="checkbox" v-model="action.filters.showUnscheduledPatients"
                                       @change="function (e) { changeUnscheduledPatients(index, e); }"/>
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
                                <span class="asap_label" style="font-weight: bold">ASAP</span>
                                <a class='my-tool-tip' data-toggle="tooltip" data-placement="top"
                                   style="color: #000;"
                                   title="Tick to schedule as:'As soon as possible'">
                                    <input v-model="action.data.asapChecked"
                                           id="asap"
                                           type="checkbox"
                                           name="asap_check"
                                           style=" float: right;margin-top: -14%;"
                                           :disabled="action.disabled">
                                </a>

                                <input class="form-control height-40" type="date" name="scheduled_date"
                                       v-model="action.data.date"
                                       :disabled="action.disabled" required/>
                            </td>
                            <td>
                                <input id="window_start" class="form-control height-40" type="time" name="window_start"
                                       v-model="action.data.startTime"
                                       :disabled="action.data.asapChecked || action.disabled" required/>
                            </td>
                            <td>
                                <input id="window_end" class="form-control height-40" type="time" name="window_end"
                                       v-model="action.data.endTime"
                                       :disabled="action.data.asapChecked || action.disabled" required/>
                            </td>
                            <td>
                                <input type="checkbox" id="is_manual"
                                       name="is_manual" v-model="action.data.isManual"
                                       :disabled="action.disabled"/>
                            </td>
                            <td>
                                <input class="form-control height-40" type="text" name="text" v-model="action.data.text"
                                       :disabled="action.disabled"/>
                            </td>
                            <td>
                                <div v-show="action.showFamilyOverride">
                                    <input type="checkbox" id="family_override"
                                           name="family_override"
                                           v-model="action.data.familyOverride"
                                           :disabled="action.disabled"/>
                                </div>
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

                <div>
                    <div class="alert alert-danger hasNot" v-if="hasNotAvailableNurses">
                        No available nurses for selected patient
                    </div>
                    <div class="alert alert-warning"
                         v-if="hasPatientInDraftMode || hasPatientInNotInAcceptableCcmStatus || hasNonMatchingPatientNurseLanguage">
                        Action not allowed:
                        <span v-if="hasPatientInDraftMode">
                            Care plan is in draft mode. QA the care plan first.
                        </span>
                        <span v-if="hasPatientInNotInAcceptableCcmStatus">
                            Patient's CCM status is one of withdrawn, paused or unreachable.
                        </span>
                        <span v-if="hasNonMatchingPatientNurseLanguage">
                            Care Coach does not speak patient's preferred contact language.
                        </span>
                    </div>
                </div>

                <br/>
                <div class="row">
                    <div class="btn btn-primary btn-xs add-activity" @click="addNewAction">
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
        familyOverride: 0,
        asapChecked: 0
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
            selectedPatientNurseLanguageDoesNotMatch: false,

            //CPM-1580 system has a command that deletes all calls with unreachable/paused/withdrawan patients
            //every 5 minutes. better stop users from creating such calls from UI.
            selectedPatientIsNotInAcceptableCcmStatus: false,

            nursesForSelect: [],
            patientsForSelect: [],
            skipRefreshingPatients: false //used when patient is already selected

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
                    UNASSIGNED_VALUE,
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

                actions: []
            }
        },
        computed: {
            hasNotAvailableNurses() {
                return this.actions.filter(x => x.data.practiceId && x.nursesForSelect.length === 0).length > 0 && !this.loaders.nurses;
            },
            hasNonMatchingPatientNurseLanguage() {
                return this.actions.filter(x => x.selectedPatientNurseLanguageDoesNotMatch).length > 0;
            },
            hasPatientInDraftMode() {
                return this.actions.filter(x => x.selectedPatientIsInDraftMode).length > 0;
            },
            hasPatientInNotInAcceptableCcmStatus() {
                return this.actions.filter(x => x.selectedPatientIsNotInAcceptableCcmStatus).length > 0;
            },
            hasToConfirmFamilyOverrides() {
                return this.actions.filter(x => x.showFamilyOverride).length > 0;
            },
            showPracticeColumn() {
                return this.practices.length > 1;
            },
        },

        methods: {
            setNursesForSelect(actionIndex) {
                this.actions[actionIndex].nursesForSelect = [
                    UNASSIGNED_VALUE,
                    ...this.actions[actionIndex].nurses.map(nurse => ({
                        label: nurse.full_name + (nurse.roles.includes('care-center-external') ? ' (in-house)' : ''),
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

                const newActionIndex = this.actions.length;
                this.actions.push(getNewAction());

                this.getPatients(newActionIndex);
                this.getPractices(newActionIndex);

            },
            removeAction(id) {
                this.$delete(this.actions, id);
            },
            selectedPatient(actionIndex) {
                return (this.actions[actionIndex].patients.find(patient => patient.id === this.actions[actionIndex].data.patientId) || {});
            },
            selectedNurse(actionIndex) {
                return (this.actions[actionIndex].nurses.find(nurse => nurse.id === this.actions[actionIndex].data.nurseId) || {});
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
                    } else {
                        this.actions[actionIndex].selectedPracticeData = UNASSIGNED_VALUE;
                    }
                }
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
            changePatient(actionIndex, patient) {
                if (patient) {
                    this.actions[actionIndex].data.patientId = patient.value;
                    const selectedPatient = this.selectedPatient(actionIndex);
                    this.setPractice(actionIndex, selectedPatient.program_id);
                    this.actions[actionIndex].selectedPatientIsInDraftMode = this.isPatientInDraftMode(selectedPatient);
                    this.actions[actionIndex].selectedPatientIsNotInAcceptableCcmStatus = this.isPatientInNotInAcceptableCcmStatus(selectedPatient);
                    //if there is a nurse selected
                    const selectedNurse = this.selectedNurse(actionIndex);
                    if (Object.keys(selectedNurse).length) {
                        this.actions[actionIndex].selectedPatientNurseLanguageDoesNotMatch = this.isNonMatchingPatientNurseLanguage(selectedPatient, selectedNurse);
                    } else {
                        this.actions[actionIndex].selectedPatientNurseLanguageDoesNotMatch = false;
                    }
                }
            },
            changePractice(actionIndex, practice) {
                if (practice) {

                    //reset selected patient only if practice is different
                    if (this.actions[actionIndex].data.practiceId !== practice.value && !this.actions[actionIndex].skipRefreshingPatients) {
                        this.actions[actionIndex].selectedPatientData = UNASSIGNED_VALUE;
                    }
                    //always reset selected nurse
                    this.actions[actionIndex].selectedNurseData = UNASSIGNED_VALUE;

                    this.actions[actionIndex].data.practiceId = practice.value;

                    //in case skip was set, we reset
                    this.actions[actionIndex].skipRefreshingPatients = false;

                    return Promise.all([this.getPatients(actionIndex), this.getNurses(actionIndex)])
                }
                return Promise.resolve([])
            },
            isNonMatchingPatientNurseLanguage(patient, nurse) {
                if (!patient.preferred_contact_language || patient.preferred_contact_language.toUpperCase() === 'EN') {
                    return false;
                }
                // nurse.spanish is one of [0,1]
                return !nurse.spanish;
            },
            isPatientInDraftMode(patient) {
                return patient.status === 'draft';
            },
            isPatientInNotInAcceptableCcmStatus(patient) {
                return ['withdrawn', 'paused', 'unreachable'].indexOf(patient.ccm_status) > -1;
            },
            changeNurse(actionIndex, nurse) {
                if (nurse) {
                    this.actions[actionIndex].data.nurseId = nurse.value;
                    const selectedPatient = this.selectedPatient(actionIndex);
                    //if there is a patient selected
                    if (Object.keys(selectedPatient).length) {
                        const selectedNurse = this.selectedNurse(actionIndex);
                        this.actions[actionIndex].selectedPatientNurseLanguageDoesNotMatch = this.isNonMatchingPatientNurseLanguage(selectedPatient, selectedNurse);
                    } else {
                        this.actions[actionIndex].selectedPatientNurseLanguageDoesNotMatch = false;
                    }
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
                    .get(rootUrl(`api/practices?admin-only=true`))
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

                        if (!this.showPracticeColumn) {
                            //0 is UNDEFINED option, so this.practicesForSelect[1]
                            this.changePractice(actionIndex, this.practicesForSelect[1]);
                        }

                    }).catch(err => {
                        this.loaders.practices = false;
                        this.errors.practices = err.message;
                        console.error('add-action:practices', err)
                    })
            },
            getPatients(actionIndex) {

                if (!this.actions[actionIndex].data.practiceId) {
                    return Promise.resolve(null);
                }

                return this.actions[actionIndex].filters.showUnscheduledPatients ?
                    this.getUnscheduledPatients(actionIndex) :
                    this.getPracticePatients(actionIndex);
            },
            getUnscheduledPatients(actionIndex) {
                this.loaders.patients = true
                const practice_addendum = this.actions[actionIndex].data.practiceId ? `practices/${this.actions[actionIndex].data.practiceId}/` : '';
                return this.axios.get(rootUrl(`api/${practice_addendum}patients/without-scheduled-activities`)).then(response => {
                    this.loaders.patients = false;
                    const pagination = response.data;
                    console.log('add-action:patients:unscheduled', pagination);
                    return pagination;
                }).then((pagination) => {
                    this.actions[actionIndex].patients = ((pagination || {}).data || [])
                        .map(patient => {
                            patient.name = patient.full_name;
                            patient.preferred_contact_language = patient.patient_info ? patient.patient_info.preferred_contact_language : null;
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
                        const patient = action.patients.find(patient => patient.id === data.patientId);
                        const assignedNurse = action.nurses.find(nurse => nurse.id === data.nurseId);
                        patients.push(Object.assign({}, patient, {nurseSpanishSpeaking: (assignedNurse ? assignedNurse : {}).spanish}));
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
                            asap: data.asapChecked,
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

                const nonMatchingLanguage = patients.filter(x => this.isNonMatchingPatientNurseLanguage(x, {spanish: x.nurseSpanishSpeaking}));
                if (nonMatchingLanguage.length) {
                    Event.$emit('notifications-add-action-modal:create', {
                        text: `Action not allowed: Care Coach does not speak these patients' [${nonMatchingLanguage.map(x => x.name || x.full_name).join(', ')}] preferred contact language`,
                        type: 'warning'
                    });
                    return;
                }

                //if any patient has status draft, we do not allow creation
                const draftPatients = patients.filter(x => this.isPatientInDraftMode(x));
                if (draftPatients.length) {
                    Event.$emit('notifications-add-action-modal:create', {
                        text: `Action not allowed: This patients' [${draftPatients.map(x => x.name || x.full_name).join(', ')}] care plan is in draft mode. QA the care plan before scheduling a call`,
                        type: 'warning'
                    });
                    return;
                }

                const ccmStatusNotAcceptable = patients.filter(x => this.isPatientInNotInAcceptableCcmStatus(x));
                if (ccmStatusNotAcceptable.length) {
                    Event.$emit('notifications-add-action-modal:create', {
                        text: `Action not allowed: This patients' [${draftPatients.map(x => x.name || x.full_name).join(', ')}] CCM status is one of withdrawn, paused or unreachable`,
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
                            const errors = actions.filter(x => x.errors && (x.errors.length > 0 || Object.keys(x.errors).length > 0));
                            if (errors.length) {

                                //we have to go through all calls and display if call was saved or not
                                actions.forEach((action, index) => {

                                    if (action.errors && (action.errors.length > 0 || Object.keys(action.errors).length > 0)) {

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
                                        Event.$emit('actions:add', action);
                                    }

                                });

                            } else {
                                this.resetForm();
                                Event.$emit("modal-add-action:hide");
                                actions.forEach(action => {
                                    Event.$emit('actions:add', action);
                                });

                                console.log('actions:add', actions);
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
        },
        created() {
            this.addNewAction();
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
                //we put a setTimeout in case a modal is already shown
                //i.e. Modal unscheduled patients is visible
                setTimeout(() => {
                    const el = '.modal-container';
                    waitForEl(el, () => {
                        $(el).css('width', document.body.offsetWidth * 0.95);
                    });

                    const el2 = "a.my-tool-tip";
                    waitForEl(el2, () => {
                        //initialize tooltips
                        $(el2).tooltip();
                    });
                }, 100);
            });

            Event.$on('add-action-modals:set', (data) => {
                if (data) {
                    if (data.practiceId) {
                        this.actions[this.actions.length - 1].skipRefreshingPatients = true;
                        this.actions[this.actions.length - 1].data.patientId = data.practiceId;
                        this.actions[this.actions.length - 1].selectedPracticeData = {
                            label: data.practiceName,
                            value: data.practiceId
                        };
                    }
                    if (data.patientId) {
                        console.log(data);
                        this.actions[this.actions.length - 1].data.patientId = data.patientId;
                        this.actions[this.actions.length - 1].selectedPatientData = {
                            label: data.patientName,
                            value: data.patientId
                        };
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
        width: 100%;
        table-layout: fixed;
        margin-left: -10px;
        border-collapse: separate;
        border-spacing: 0 16px;
    }

    /* Table with a Practices column */
    .modal-add-action table.add-actions th.sub-type.with-practice-column {
        width: 12%;
    }

    .modal-add-action table.add-actions th.practices {
        width: 11%;
    }

    .modal-add-action table.add-actions th.patients.with-practice-column {
        width: 14%;
    }

    .modal-add-action table.add-actions th.patients-tool-tip.with-practice-column {
        width: 2%;
    }

    .modal-add-action table.add-actions th.nurses.with-practice-column {
        width: 12%;
    }

    .modal-add-action table.add-actions th.date.with-practice-column {
        width: 14%;
    }

    .modal-add-action table.add-actions th.start-time.with-practice-column {
        width: 11%;
    }

    .modal-add-action table.add-actions th.end-time.with-practice-column {
        width: 11%;
    }

    .modal-add-action table.add-actions th.end-time-tooltip.with-practice-column {
        width: 2%;
    }

    .modal-add-action table.add-actions th.notes.with-practice-column {
        width: 10%;
    }

    .modal-add-action table.add-actions th.remove.with-practice-column {
        width: 3%;
    }

    .modal-add-action table.add-actions th.family-override.with-practice-column {
        width: 2%;
    }

    .modal-add-action table.add-actions th.sub-type {
        width: 10%;
    }

    .modal-add-action table.add-actions th.patients {
        width: 15%;
    }

    .modal-add-action table.add-actions th.patients-tool-tip {
        width: 2%;
    }

    .modal-add-action table.add-actions th.nurses {
        width: 16%;
    }

    .modal-add-action table.add-actions th.date {
        width: 12%;
    }

    .modal-add-action table.add-actions th.start-time {
        width: 10%;
    }

    .modal-add-action table.add-actions th.end-time {
        width: 10%;
    }

    .modal-add-action table.add-actions th.end-time-tooltip {
        width: 2%;
    }

    .modal-add-action table.add-actions th.notes {
        width: 17%;
    }

    .modal-add-action table.add-actions th.remove {
        width: 3%;
    }

    .modal-add-action table.add-actions th.family-override {
        width: 3%;
    }

    .modal-add-action .loader {
        position: absolute;
        right: 3px;
        top: 7px;
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

    .height-40 {
        height: 40px;
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
        height: 40px;
        position: relative;
        overflow: hidden;
    }

    .modal-add-action .modal-body {
        min-height: 300px;
        margin-left: 20px;
        margin-top: 1px;
    }

    .selected-tag {
        height: 100%;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    .add-activity {
        margin-left: -6px;
    }

    .modal-header h3 {
        margin-top: 0;
        margin-left: -2px;
        color: #000;
    }

    .v-select .open-indicator {
        visibility: visible;
    }

    .v-select .dropdown-toggle .clear {
        display: none;
    }

    .v-select .vs__selected-options {
        max-width: 86%;
    }

    .v-select .dropdown-menu > .highlight > a {
        display: inline-block;
    }

    .asap_label {
        margin-left: 58%;
        position: absolute;
        margin-top: -16%;
    }
</style>
