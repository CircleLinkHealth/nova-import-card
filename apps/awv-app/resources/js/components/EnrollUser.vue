<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Enroll {{patientName}} into AWV</div>

                    <div class="card-body">

                        <div class="spinner-overlay" v-show="waiting">
                            <div class="text-center">
                                <mdb-icon icon="spinner" :spin="true"/>
                            </div>
                        </div>

                        <p>
                            Please confirm by clicking below to enroll {{patientName}} into AWV.
                        </p>
                        <p>
                            <mdb-btn @click="enrollUser" :disabled="waiting || success">
                                Enroll
                            </mdb-btn>
                        </p>
                        <p>
                            <mdb-alert v-if="error" color="danger">
                                {{error}}
                            </mdb-alert>

                            <mdb-alert v-if="success" color="success">
                                User enrolled. You can close this window.
                            </mdb-alert>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import {mdbAlert, mdbBtn, mdbIcon} from 'mdbvue';

    export default {
        name: "EnrollUser",
        components: {mdbBtn, mdbAlert, mdbIcon},
        props: ['patientName', 'patientId'],
        data() {
            return {
                waiting: false,
                success: false,
                error: null,
            }
        },
        methods: {
            enrollUser() {
                this.waiting = true;
                this.success = false;
                this.error = null;
                axios
                    .post(`/manage-patients/${this.patientId}/enroll`)
                    .then(response => {
                        this.waiting = false;
                        this.success = true;
                    })
                    .catch(err => {
                        this.waiting = false;
                        this.success = false;
                        this.handleError(err);
                    });
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
        mounted() {

        },
        created() {

        }
    }
</script>

<style scoped>

</style>
