<template>
    <mdb-modal v-on:close="cancel">
        <mdb-modal-header>
            <mdb-modal-title>Add AWV Patient</mdb-modal-title>
        </mdb-modal-header>
        <mdb-modal-body>

            <mdb-container>

                <mdb-row>
                    <mdb-col>
                        <div class="spinner-overlay" v-show="waiting">
                            <div class="text-center">
                                <font-awesome-icon icon="spinner" :spin="true"/>
                            </div>
                        </div>
                    </mdb-col>
                </mdb-row>

                <mdb-row>
                    <mdb-col>

                    </mdb-col>
                </mdb-row>

                <mdb-row>
                    <mdb-col>
                        <mdb-alert v-if="error" color="danger">
                            {{error}}
                        </mdb-alert>
                    </mdb-col>
                </mdb-row>

            </mdb-container>

        </mdb-modal-body>
        <mdb-modal-footer>
            <mdb-btn color="warning" @click.native="cancel">Cancel</mdb-btn>
            <mdb-btn color="primary" @click.native="save" :disabled="waiting">Save</mdb-btn>
        </mdb-modal-footer>
    </mdb-modal>
</template>

<script>

    import {
        mdbAlert,
        mdbBtn,
        mdbInput,
        mdbModal,
        mdbModalBody,
        mdbModalFooter,
        mdbModalHeader,
        mdbModalTitle,
        mdbCol,
        mdbContainer,
        mdbRow
    } from 'mdbvue';

    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faSpinner} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

    library.add(faSpinner);

    export default {
        name: "SendLinkModal",
        components: {
            FontAwesomeIcon,
            mdbModal,
            mdbModalBody,
            mdbModalFooter,
            mdbModalHeader,
            mdbModalTitle,
            mdbBtn,
            mdbAlert,
            mdbInput,
            mdbContainer,
            mdbCol,
            mdbRow
        },
        props: ['options'],
        data() {
            return {
                waiting: false,
                error: null,
            };
        },
        created() {

        },
        mounted() {
        },
        methods: {
            validate() {

                if (this.options.debug) {
                    return true;
                }

                return this.validateEmail(this.customEmail);
            },

            validateEmail(email) {
                const re = /\S+@\S+\.\S+/;
                return re.test(email);
            },

            save() {

                if (!this.validate()) {
                    return;
                }

                this.error = null;
                this.waiting = true;

                const url = `/manage-patients/${this.patientId}/send-link/${this.isVitals ? 'vitals' : 'hra'}`;
                let target;
                if (this.selectedChannel === CHANNEL_MAIL) {
                    target = this.selectedEmail === 'other' ? this.customEmail : this.selectedEmail;
                }
                else {
                    target = this.selectedPhoneNumber === 'other' ? this.customPhoneNumber : this.selectedPhoneNumber;
                    target = this.sanitizePhoneNumber(target);
                }

                const req = {
                    target_patient_id: this.patientId,
                    channel: this.selectedChannel,
                    channel_value: target,
                };

                axios.post(url, req)
                    .then(resp => {
                        this.waiting = false;
                        if (typeof this.options.success !== 'undefined'){
                            //this is for send assessmen link component, so we trigger the success alert
                            this.options.success();
                        }
                        this.options.onDone();

                    })
                    .catch(error => {
                        this.waiting = false;
                        this.handleError(error);
                    })
            },
            cancel() {
                this.options.onDone();
            },

            handleError(error) {
                console.log(error);
                if (error.response && error.response.status === 504) {
                    this.error = "Server took too long to respond. Please try again.";
                }
                else if (error.response && error.response.status === 500) {
                    this.error = "There was an error with our servers. Please contact CLH support.";
                    console.error(error.response.data);
                }
                else if (error.response && error.response.status === 404) {
                    this.error = "Not Found [404]";
                }
                else if (error.response && error.response.status === 419) {
                    this.error = "Not Authenticated [419]";
                    //reload the page which will redirect to login
                    window.location.reload();
                }
                else if (error.response && error.response.data) {
                    const errors = [error.response.data.error];
                    Object.keys(error.response.data.errors || []).forEach(e => {
                        errors.push(error.response.data.errors[e]);
                    });
                    this.error = errors.join('<br/>');
                } else {
                    this.error = error.message;
                }
            }
        },
        computed: {
        }
    }
</script>

<style scoped>

    .container {
        position: relative;
    }

</style>
