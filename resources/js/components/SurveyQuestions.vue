<template>
    <div class="container">
        <!--Survey welcome note-->
        <div class="survey-container">
            <div v-if="welcomeStage" class="practice-title">
                <label id="title">[Practice Name]
                    Dr. [doctor last name]’s Office</label>
            </div>
            <div v-if="welcomeStage" class="card-body">
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
            <div class="questions-box"
                 v-if="questionsStage"
                 v-for="(question, index) in questions">
                <div v-show="index >= questionIndex" class="question">
                    <div v-if=""><!--data-aos="fade-up"-->
                        {{question.id}}{{'.'}} {{question.body}}
                        <!--Questions Answer Type-->
                        <div class="question-answer-type">
                            <question-type-text :question="question" v-if="question.type.type === 'text'"></question-type-text>
                            <question-type-checkbox
                                    v-if="question.type.type === 'checkbox'"></question-type-checkbox>
                            <question-type-range v-if="question.type.type === 'range'"></question-type-range>
                            <question-type-number
                                    v-if="question.type.type === 'number'"></question-type-number>
                            <question-type-radio :question="question"
                                                 v-if="question.type.type === 'radio'"></question-type-radio>
                            <question-type-date v-if="question.type.type === 'date'"></question-type-date>
                        </div>
                    </div>
                </div>
            </div>
            <call-assistance v-if="callAssistance" @closeCallAssistanceModal="hideCallHelp"></call-assistance>
            <br>
        </div>
        <!--bottom-navbar-->
        <div class="bottom-navbar">
            <!--phone assistance-->
            <div class="row">
                <div v-if="showPhoneButton" class="call-assistance col-lg-1">
                    <button type="button"
                            class="btn btn-default"
                            @click="showCallHelp">
                        <i class="fa fa-phone" aria-hidden="true"></i>
                    </button>
                </div>
                <div v-if="!showPhoneButton" class="call-assistance col-lg-1">
                    <button type="button"
                            class="btn btn-default"
                            @click="hideCallHelp">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div v-if="questionsStage">
                    <!--progress bar-->
                    <div class="row mb-1" style="margin-left: 380px;">
                        <div class="progressbar-label col-lg-4 col-sm-2">{{this.progressCount}} of {{totalQuestions}}
                            completed
                        </div>
                        <div class="col-lg-4 col-sm-10 pt-1">
                            <b-progress style="width: 280px; height:10px; margin-left: -40%;"
                                        :value="progressCount"></b-progress>
                        </div>
                    </div>
                </div>
                <!--scroll buttons-->
                <div class="row">
                    <div class="scroll-buttons">
                        <button type="button"
                                id="scroll-down"
                                class="btn btn-sm next"
                                @click="scrollDown">
                            <i class="fas fa-angle-down"></i>
                        </button>
                        <button v-if="" type="button"
                                id="scroll-up"
                                class="btn btn-sm next"
                                @click="scrollUp">
                            <i class="fas fa-angle-up"></i>
                        </button>
                    </div>
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
    import callAssistance from "./callAssistance";
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
    import subQuestions from "./subQuestions";
    import mainQuestions from "./mainQuestions";
    import {EventBus} from '../event-bus';
    import AOS from 'aos';
    import 'aos/dist/aos.css';
    import BootstrapVue from 'bootstrap-vue'
    import 'bootstrap/dist/css/bootstrap.css'
    import 'bootstrap-vue/dist/bootstrap-vue.css'


    AOS.init({
        duration: 1200,
    });

    export default {
        props: ['surveydata'],

        components: {
            'main-questions': mainQuestions,
            'sub-questions': subQuestions,
            'question-type-text': questionTypeText,
            'question-type-checkbox': questionTypeCheckbox,
            'question-type-range': questionTypeRange,
            'question-type-number': questionTypeNumber,
            'question-type-radio': questionTypeRadio,
            'question-type-date': questionTypeDate,
            'call-assistance': callAssistance,
            'bootstrap-vue': BootstrapVue
        },

        data() {
            return {
                showPhoneButton: true,
                questionsStage: false,
                welcomeStage: true,
                callAssistance: false,
                questions: [],
                subQuestions: [],
                shouldShowQuestion: false,
                questionIndex: 0,
                progressCount: 0,
            }
        },
        computed: {
            subQuestionsConditions() {
                return this.subQuestions.flatMap(function (subquestion) {
                    return subquestion.conditions;
                });
            },

            questionsOrder() {
                return this.questions.flatMap(function (q) {
                    return q.pivot.order;
                });

            },

            totalQuestions() {
                return this.questions.length - this.subQuestions.length;
            }
        },

        methods: {
            showCallHelp() {
                this.callAssistance = true;
                this.showPhoneButton = false;
            },

            hideCallHelp() {
                this.callAssistance = false;
                this.showPhoneButton = true;
            },

            showQuestions() {
                this.questionsStage = true;
                this.welcomeStage = false;
            },

            scrollDown() {

            },

            scrollUp() {

            },

            showSubQuestion(conditions) {
                this.shouldShowQuestion = true;
            },

            handleRadioInputs(answerVal, questionOrder, questionId) {

                const conditions = this.subQuestionsConditions.filter(function (q) {
                    return q.related_question_order_number === questionOrder
                        && q.related_question_expected_answer === answerVal
                });

                if (conditions.length !== 0) {
                    this.showSubQuestion(conditions);
                }

                this.questionIndex++;
                this.updateProgressBar();
            },

            handleNumberInputs() {
                this.questionIndex++;
                this.updateProgressBar();
            },

            handleTextInputs() {
                this.questionIndex++;
                this.updateProgressBar();
            },

            addInput() {

            },

            updateProgressBar() {
                this.progressCount++;
            },

        },
        mounted() {
            EventBus.$on('showSubQuestions', (answerVal, questionId) => {
                this.handleRadioInputs(answerVal, questionId)
            });

            EventBus.$on('handleNumberType', () => {
                this.handleNumberInputs();
            });

            EventBus.$on('handleTextType', () => {
                this.handleTextInputs();
            });
        },
        created() {
            const questionsData = this.surveydata.survey_instances[0].questions.map(function (q) {
                const result = Object.assign(q, {answer_types: [q.answer_type]});
                return result;
            });
            const questions = questionsData.filter(question => !question.optional);
            const subQuestions = questionsData.filter(question => question.optional);

            this.questions.push(...questionsData);
            this.subQuestions.push(...subQuestions);
        },

    }
</script>

<style scoped>
    .questions-box {
        padding-top: 5%;
        padding-left: 12%;
    }

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
        padding-top: 12px;
    }

    .bottom-navbar {
        background-color: #ffffff;
        border-bottom: 1px solid #808080;
        border-left: 1px solid #808080;
        border-right: 1px solid #808080;
        min-height: 90px;
        height: 90px;
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

    .survey-container {
        margin-top: 50px;
        background-color: #f2f6f9;
        border-top: 1px solid #808080;
        border-left: 1px solid #808080;
        border-right: 1px solid #808080;
        width: 100%;
        min-height: 100%;
        max-height: 600px;
        overflow-y: scroll;
    }

    .survey-container::-webkit-scrollbar { width: 0 !important }

    .scroll-buttons {
        margin-left: 990px;
        margin-top: 20px;
    }

    #scroll-up, #scroll-down {
        background-color: #50b2e2;
        width: 51px;
        height: 51px;
        border-radius: 5px;
        margin-right: 15px;
        float: right;
    }

    .welcome-icon {
        width: 108px;
        margin-left: 490px;
        margin-top: 20px;
    }

    .fa-phone {
        transform: scaleX(-1);
        color: #ffffff;
    }

    .fa-times {
        width: 20px;
        height: 20px;
        color: #ffffff;
    }

    .call-assistance {
        display: table-cell;
    }

    .btn-default {
        height: 50px;
        width: 50px;
        border-radius: 50%;
        border: solid 1px #4aa5d2;
        background-color: #50b2e2;
        margin-top: 15px;
    }

    .progressbar-label {
        position: relative;
    }



</style>
