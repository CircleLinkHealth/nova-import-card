<template>
    <div class="container main-container">
        <!--Survey welcome note-->
        <div class="survey-container">
            <div class="card-body">
                <div class="welcome-icon-container">
                    <img src="../../assets/images/notepad.png"
                         class="welcome-icon" alt="welcome icon">
                </div>

                <div class="survey-main-title">
                    <label id="sub-title">Thank You!</label>
                </div>
                <div class="align-items-center">
                    <div class="survey-sub-welcome-text">
                        Thank you for completing your AWV Health Risk Assessment. When your Vitals are taken in the
                        office, please hand over your phone to the clinician so they can enter your information.
                    </div>
                    <div class="survey-sub-welcome-text">
                        <b>If you're the provider</b>, please click Login below to input {{patientsName}} Vitals
                        information.
                    </div>

                </div>

                <div class="btn-start-container">
                    <mdb-btn color="primary" class="btn-login" @click="login">Provider Login for {{patientsName}}'s
                        Vitals
                    </mdb-btn>
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
        props: ['patientsName'],
        components: {mdbBtn},
        methods: {
            login() {
                window.location.href = '/auth/login';
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

    @media (min-width: 576px) {
        .btn-login {
            margin-top: 60px;
            margin-bottom: 20px;
            font-size: 18px;
            letter-spacing: 1px;
        }
    }
</style>
