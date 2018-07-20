<template>
    <div>
        <br/>
        <span>
            <b>Next Call</b>:
            <loader v-if="loaders.nextCall"></loader>
            <span v-if="!loaders.nextCall">
                {{displayDate}}
            </span>
        </span>

        <span v-if="!loaders.nextCall && isCareCenter"
              @click="showEditCallModal"
              class="glyphicon glyphicon-pencil"
              style="cursor:pointer;">
        </span>

        <edit-call-modal :patient-preferences="patientPreferences"></edit-call-modal>
        <br/>
    </div>
</template>
<script>

    import EditCallModal from './edit-call.modal.vue';
    import LoaderComponent from './loader';
    import {Event} from 'vue-tables-2';
    import {today} from "../util/today";
    import {rootUrl} from "../app.config";

    const defaultNextCall = {
        id: null,
        attempt_note: '',
        window_start: '09:00',
        window_end: '17:00',
        scheduled_date: today(),
        inbound_cpm_id: null,
        outbound_cpm_id: null,
        is_manual: 1
    };

    export default {
        name: 'patient-next-call',
        props: [
            'patientId',
            'patientPreferences',
            'isCareCenter'
        ],
        components: {
            'edit-call-modal': EditCallModal,
            'loader': LoaderComponent,
        },
        data() {
            return {
                nextCall: Object.assign({}, defaultNextCall),
                isCallBeingAddedToNote: false,
                displayDate: 'None',
                loaders: {
                    nextCall: false
                },
            }
        },
        methods: {
            getNextCall() {
                this.loaders.nextCall = true;
                const url = rootUrl(`manage-patients/${this.patientId}/calls/next`);
                this.axios.get(url)
                    .then(resp => {
                        this.loaders.nextCall = false;
                        if (resp.data && resp.data.id) {
                            this.setNextCall(resp.data);
                        }
                    })
                    .catch(err => {
                        this.loaders.nextCall = false;
                        console.error(err);
                    });
            },
            showEditCallModal() {
                Event.$emit('modal-edit-call:show', this.nextCall);
            },
            setPatientId(id) {
                //we know the patient id, so we set it.
                //its needed in case we are scheduling a new call
                this.nextCall.inbound_cpm_id = id;
            },
            setNextCall(call) {
                if (!call) {
                    return;
                }
                this.nextCall.id = call.id;
                this.nextCall.inbound_cpm_id = call.inbound_cpm_id;
                this.nextCall.outbound_cpm_id = call.outbound_cpm_id;
                this.nextCall.scheduled_date = call.scheduled_date;
                this.nextCall.window_start = call.window_start;
                this.nextCall.window_end = call.window_end;
                this.nextCall.attempt_note = call.attempt_note;
                this.nextCall.is_manual = call.is_manual;
                this.setDisplayDate();
            },
            isNextCallToday() {
                //could use a date lib here
                const today = new Date();
                const year = today.getFullYear();

                let month = today.getMonth() + 1; //starts from 0
                month = month >= 10 ? month : '0' + month;

                let day = today.getDate();
                day = day >= 10 ? day : '0' + day;

                const todayStr = `${year}-${month}-${day}`;
                return this.nextCall.scheduled_date === todayStr;
            },
            hideCallIfBeingAddedToNote(added) {
                this.isCallBeingAddedToNote = added;
                this.setDisplayDate();
            },
            setDisplayDate() {
                if (this.isCareCenter && this.isCallBeingAddedToNote && this.isNextCallToday()) {
                    this.displayDate = 'TBD';
                }
                else if (this.nextCall.id === null) {
                    //a.Schedule button will appear in case of care center
                    //b.None will be shown if not care center
                    this.displayDate = 'None';                
                    }
                else {
                    this.displayDate = `${this.nextCall.scheduled_date} @ ${this.nextCall.window_start} - ${this.nextCall.window_end}`;
                }

            },
        },
        mounted() {

            if (!this.patientId) {
                console.error("PatientNextCall component missing patient id.")
                return;
            }

            this.setPatientId(this.patientId);
            this.getNextCall();

            //emitted from EditCallModal when a new call is created/rescheduled
            Event.$on('calls:add', (call) => {
                this.setNextCall(call);
            });

            //using global App component, since event is fired from a blade.php file
            //emitted from Create Note page when check box Patient Phone Session is checked
            App.$on('create-note:with-call', (value) => {
                this.hideCallIfBeingAddedToNote(value);
            });
        }
    }
</script>