<template>
    <div class="container main-container">
        <!--Survey welcome note-->
        <div class="survey-container">
            <div class="card-body">
                <div class="welcome-icon-container">
                    <img src="../../images/doctors.png"
                         class="welcome-icon" alt="welcome icon">
                </div>

                <div class="survey-main-title">
                    <label id="sub-title">Thank You!</label>
                </div>
                <div class="align-items-center">
                    <div class="survey-sub-welcome-text">
                        Thank you for completing your AWV Health Risk Assessment. If your vitals are being taken
                        in-office now, please hand over your phone to the clinician so they can enter your information.
                    </div>
                    <div class="survey-sub-welcome-text">
                        If you're not in the office, {{doctorName}}'s office will see you at your upcoming
                        appointment. Please contact {{doctorName}}'s office if you don't have one scheduled.
                    </div>
                    <div class="survey-sub-welcome-text" v-if="isProviderLoggedIn">
                        <b>If you're the provider/staff</b>, please click below to input {{patientName}} Vitals
                        information.
                    </div>
                    <div class="survey-sub-welcome-text" v-else>
                        <b>If you're the provider/staff</b>, please click Login below to input {{patientName}} Vitals
                        information.
                    </div>
                </div>

                <div class="btn-start-container">
                    <mdb-btn v-if="isProviderLoggedIn" color="primary" class="btn-login" @click="showVitalsSurvey">
                        Proceed to enter {{patientName}}'s Vitals
                    </mdb-btn>
                    <mdb-btn v-else color="primary" class="btn-login" @click="logout">
                        Login to enter {{patientName}}'s Vitals
                    </mdb-btn>

                    <form id="logout-form" action="/logout" method="POST" style="display: none;">
                        <input type="hidden" name="redirectTo" :value="getVitalsRoute()">
                    </form>

                </div>

                <div class="by-circlelink text-center">
                    ⚡️ by CircleLink Health
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import {mdbBtn} from 'mdbvue';

    export default {
        name: "VitalsSurveyWelcome",
        props: ['patientId', 'patientName', 'isProviderLoggedIn', 'doctorName'],
        components: {mdbBtn},
        methods: {

            getVitalsRoute() {
                return `/survey/vitals/${this.patientId}`;
            },

            showVitalsSurvey() {
                window.location.href = this.getVitalsRoute();
            },

            logout() {
                const token = document.head.querySelector('meta[name="csrf-token"]');
                $('<input>')
                    .attr({
                        type: 'hidden',
                        name: '_token',
                        value: token.content
                    })
                    .appendTo('#logout-form');

                $('#logout-form').submit();
            }
        }
    }
</script>

<style scoped>

    .btn-login {
        padding-left: 10px;
        padding-right: 10px;
        margin-top: 30px;
        margin-bottom: 20px;
        font-family: Poppins, serif;
        font-size: 10px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 0.6px;
        text-transform: none;
        text-align: center;
    }

    @media (min-width: 519px) {
        .btn-login {
            margin-top: 60px;
            margin-bottom: 20px;
            font-size: 18px;
            letter-spacing: 1px;
        }
    }
</style>
