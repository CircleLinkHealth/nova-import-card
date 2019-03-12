<template>
    <div class="container">
        <!--Survey welcome note-->
        <div class="card">
            <div v-show="!questionsVisible" class="practice-title">
                <label id="title">[Practice Name]
                    Dr. [doctor last name]’s Office</label>
            </div>
            <div v-show="!questionsVisible" class="card-body">
                <img src="https://drive.google.com/uc?export=view&id=14yPR6Z8coudiAzEMTSVQK80BVyZjjqVg"
                     class="welcome-icon" alt="welcome icon">
                <div class="survey-main-title">
                    <label id="sub-title">Annual Wellness
                        Survey Login</label>
                </div>
                <div class="survey-sub-welcome-text">Welcome to your
                    Annual Wellness Visit (AWV) Questionnaire! Understanding your health is of upmost importance to us,
                    so thank you for taking time to fill this out.
                    If there’s any question you have trouble answering, feel free to click the call button on the bottom
                    left and a representative will help when you call the number. If you skip any questions, our reps
                    will also reach out shortly. Thanks!
                </div>

                <a class="btn btn-primary" @click="showQuestions">Start</a>

                <div class="by-circlelink">
                    ⚡️ by CircleLink Health
                </div>
            </div>
            <!--Questions-->
            <div v-show="questionsVisible"
                 v-for="question in questions">
                {{question.body}}
                <!--Questions Answer Type-->
                 <question-type-text v-if="question.type.answer_type === 'text'"></question-type-text>
                 <question-type-checkbox v-if="question.type.answer_type === 'checkbox'"></question-type-checkbox>
                 <question-type-range v-if="question.type.answer_type === 'range'"></question-type-range>
                 <question-type-number v-if="question.type.answer_type === 'number'"></question-type-number>
                 <question-type-radio v-if="question.type.answer_type === 'radio'"></question-type-radio>
                 <question-type-date v-if="question.type.answer_type === 'date'"></question-type-date>

            </div>
            <!--bottom-navbar-->
            <br>
            <div class="bottom-navbar">
                <div class="call-assistance">
                    <button type="button" class="btn btn-default btn-sm">
                        <span class="glyphicon glyphicon-earphone">Call</span>
                    </button>
                </div>
                <div v-show="questionsVisible" class="next-previous-buttons">
                    <button type="button" class="btn btn-sm next" @click="nextQuestions">Next</button>
                </div>
            </div>
        </div>
    </div>

</template>


<script>

    import questionTypeText from "./questionTypeText";
    import questionTypeCheckbox from "./questionTypeCheckbox";
    import questionTypeRange from "./questionTypeRange";
    import questionTypeNumber from "./questionTypeNumber";
    import questionTypeRadio from "./questionTypeRadio";
    import questionTypeDate from "./questionTypeDate";

    export default {
        props: ['questions'],

        mounted() {
            console.log('Component mounted.')

        },

        components: {
            'question-type-text': questionTypeText,
            'question-type-checkbox': questionTypeCheckbox,
            'question-type-range': questionTypeRange,
            'question-type-number': questionTypeNumber,
            'question-type-radio': questionTypeRadio,
            'question-type-date': questionTypeDate

        },

        data() {
            return {
                questionsVisible: false,
            }
        },
        methods: {
            showQuestions() {
                this.questionsVisible = true
            },
            nextQuestions() {

            },

            previousQuestions() {

            },
        },
    }
</script>

<style scoped>
    .practice-title {
        font-family: Poppins, sans-serif;
        font-size: 18px;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 20px;
        color: #50b2e2;
    }

    .practice-title .text-style-1 {
        font-weight: 600;
    }

    .survey-main-title {
        font-family: Poppins, sans-serif;
        font-size: 24px;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 30px;
        color: #1a1a1a;
    }

    .survey-sub-welcome-text {
        font-family: Poppins, sans-serif;
        font-size: 18px;
        font-weight: normal;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        text-align: center;
        margin-top: 25px;
        color: #1a1a1a;
    }

    .btn-primary {
        border-radius: 3px;
        background-color: #50b2e2;
        margin-top: 50px;
        margin-left: 469px;
        width: 160px;
        height: 50px;
    }

    .bottom-navbar {
        background-color: #ffffff;
        border-bottom: 1px solid #808080;
        min-height: 90px;
        margin-top: auto;

    }

    .by-circlelink {
        font-family: Poppins, sans-serif;
        font-size: 18px;
        font-weight: 600;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        margin-top: 10px;
        margin-left: 430px;
        color: #50b2e2;
    }

    .by-circlelink .text-style-1 {
        font-weight: normal;
        color: #1a1a1a;
    }

    .card {
        margin-top: 50px;
        background-color: #f2f6f9;
        border-top: 1px solid #808080;
        border-left: 1px solid #808080;
        border-right: 1px solid #808080;
        min-height: 690px;
    }

    .next-previous-buttons {
        width: 55px;
        margin-top: 10px;
        margin-bottom: 10px;
        margin-left: 1020px;
        height: 55px;
        border-radius: 5px;
        background-color: #50b2e2;
    }

    .next {
        position: center;
    }

    .previous {
        position: center;
    }

    .welcome-icon {
        width: 108px;
        margin-left: 490px;
        margin-top: 20px;
    }

    .call-assistance {

    }
</style>
