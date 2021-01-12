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
                        Thank you for completing your routine Wellness Assessment. If you don't already have an
                        in-person appointment scheduled, {{doctorsName}}'s office will reach out soon via a call or e-mail.
                        Please contact {{doctorsName}}'s office if you don't hear from us.
                    </div>
                </div>

                <div class="by-circlelink text-center">
                    ⚡️ by CircleLink Health
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "VitalsSurveyNotAuthorized",
        props: ['doctorsName'],
    }
</script>

<style scoped>

    .by-circlelink {
        font-family: Poppins, serif;
        font-size: 18px;
        font-weight: 600;
        margin-top: 20px;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        color: #50b2e2;
    }
</style>
