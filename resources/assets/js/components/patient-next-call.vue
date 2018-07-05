<template>
    <div>
        <br/>
        <span>
            <b>Next Call</b>:
            <loader v-if="loaders.nextCall"></loader>
            <span v-if="nextCall.id != null">
                {{nextCall.scheduled_date}} @ {{ nextCall.window_start }} - {{ nextCall.window_end }}
            </span>
        </span>

        <span v-if="!loaders.nextCall && isCareCenter && nextCall.id != null"
              @click="showEditCallModal"
              class="glyphicon glyphicon-pencil"
              style="cursor:pointer;">
        </span>

        <div v-if="!loaders.nextCall && isCareCenter && nextCall.id == null"
             class="btn btn-primary"
             @click="showEditCallModal">
            Schedule
        </div>

        <span v-if="!loaders.nextCall && !isCareCenter && nextCall.id == null">
            None
        </span>

        <edit-call-modal></edit-call-modal>
        <br/>
    </div>
</template>
<script>

    import EditCallModal from './edit-call.modal.vue';
    import LoaderComponent from './loader'
    import {Event} from 'vue-tables-2'
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
    };

    export default {
        name: 'patient-next-call',
        props: [
            'patientId',
            'isCareCenter'
        ],
        components: {
            'edit-call-modal': EditCallModal,
            'loader': LoaderComponent,
        },
        data() {
            return {
                nextCall: Object.assign({}, defaultNextCall),
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
            }
        },
        mounted() {

            if (!this.patientId) {
                console.error("PatientNextCall component missing patient id.")
                return;
            }
            this.setPatientId(this.patientId);
            this.getNextCall();

            Event.$on('calls:add', (call) => {
                this.setNextCall(call);
            });
        }
    }
</script>