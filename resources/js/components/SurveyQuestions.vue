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
                    <label id="sub-title">Annual Wellness Visit (AWV) Questionnaire</label>
                </div>
                <div class="survey-sub-welcome-text">Welcome to your
                    Annual Wellness Visit (AWV) Questionnaire! Understanding your health is of upmost importance to us,
                    so thank you for taking time to fill this out.
                    If there’s any question you have trouble answering, feel free to click the call button on the bottom
                    left and a representative will help when you call the number. If you skip any questions, our reps
                    will also reach out shortly. Thanks!
                </div>

                <div v-if="this.lastQuestionAnswered !== null">
                    <a class="btn btn-primary" @click="showQuestions">Start</a>
                </div>

                <div v-if="this.lastQuestionAnswered !== null">
                    <a class="btn btn-primary" @click="scrollToLastQuestion">Continue</a>
                </div>

                <div class="by-circlelink">
                    ⚡️ by CircleLink Health
                </div>
            </div>
            <!--Questions-->
            <div class="questions-box"
                 v-if="questionsStage"
                 v-for="(question, index) in questions">
                <div v-show="index >= questionIndex" class="question">
                    <div class="questions-body" v-show="true">
                        <div class="questions-title">
                            <div>
                                {{question.pivot.order}}{{question.pivot.sub_order}}{{'.'}} {{question.body}}
                            </div>
                        </div>
                        <br>
                        <!--Questions Answer Type-->
                        <div class="question-answer-type">
                            <question-type-text
                                    :question="question"
                                    :on-done-func="postAnswerAndGoToNext"
                                    v-if="question.type.type === 'text'">
                            </question-type-text>

                            <question-type-checkbox
                                    :question="question"
                                    :on-done-func="postAnswerAndGoToNext"
                                    v-if="question.type.type === 'checkbox'">
                            </question-type-checkbox>

                            <question-type-muti-select
                                    :questions="questions"
                                    :question="question"
                                    :surveyAnswers="surveyAnswers"
                                    :on-done-func="postAnswerAndGoToNext"
                                    v-if="question.type.type === 'multi_select'">
                            </question-type-muti-select>

                            <question-type-range
                                    v-if="question.type.type === 'range'">
                            </question-type-range>

                            <question-type-number
                                    :question="question"
                                    :on-done-func="postAnswerAndGoToNext"
                                    v-if="question.type.type === 'number'">
                            </question-type-number>

                            <question-type-radio
                                    :question="question"
                                    :on-done-func="postAnswerAndGoToNext"
                                    v-if="question.type.type === 'radio'">
                            </question-type-radio>

                            <question-type-date
                                    v-if="question.type.type === 'date'">
                            </question-type-date>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="call-assistance">
            <call-assistance v-if="callAssistance" @closeCallAssistanceModal="hideCallHelp"></call-assistance>
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
                        <div class="progressbar-label col-lg-6 col-sm-2">{{this.progressCount}} of {{totalQuestions}}
                            completed
                        </div>
                        <div class="progressbar col-lg-6 col-sm-10 pt-1">
                            <b-progress style="width: 280px; height:10px; margin-left: -40%; margin-top: 18%;"
                                        :value="progressCount"></b-progress>
                        </div>
                    </div>
                </div>
                <!--scroll buttons-->
                <div v-show="!welcomeStage" class="row">
                    <div class="scroll-buttons col-lg-2">
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
    import BootstrapVue from 'bootstrap-vue'
    import 'bootstrap/dist/css/bootstrap.css'
    import 'bootstrap-vue/dist/bootstrap-vue.css'
    import questionTypeMultiSelect from "./questionTypeMultiSelect";


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
            'bootstrap-vue': BootstrapVue,
            'question-type-muti-select': questionTypeMultiSelect
        },

        data() {
            return {
                showPhoneButton: true,
                questionsStage: false,
                welcomeStage: true,
                callAssistance: false,
                questions: [],
                subQuestions: [],
                instanceQuestionOrder: -1,
                shouldShowQuestion: false,
                questionIndex: 0,
                progressCount: 0,
                userId: this.surveydata.id,
                surveyInstanceId: [],
                questionIndexAnswers: [],
                surveyAnswers: [],
                conditionsLength: 0,
            }
        },

        computed: {
            subQuestionsConditions() {
                return this.subQuestions.flatMap(function (subQuestion) {
                    return subQuestion.conditions;
                });
            },

            lastQuestionAnswered() {
                return this.surveydata.survey_instances[0].pivot.last_question_answered_id;
            },

            questionsOrder() {
                return this.questions.flatMap(function (q) {
                    return q.pivot.order + q.pivot.sub_order;
                });
            },

            totalQuestions() {
                return this.questions.length - this.subQuestions.length;
            },

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
            scrollToLastQuestion() {
                this.questionsStage = true;
                this.welcomeStage = false;
                //@todo:check this again - i dont like it
                this.questionIndex = this.lastQuestionAnswered - 1;
            },
            scrollDown() {

            },

            scrollUp() {

            },


            showSubQuestionNew(index, questionOrder) {

                if (index !== questionOrder) {
                    return true;
                }
                const q = this.questions[index];
                if (q.conditions !== null) {
                    const parentQuestionAnswer = this.questionIndexAnswers[q.conditions['0'].related_question_order_number];
                    if (parentQuestionAnswer) {
                        return parentQuestionAnswer === q.conditions['0'].related_question_expected_answer;
                    }

                    return false;
                }

            },

            showSubQuestion(conditions) {
                this.shouldShowQuestion = true;

            },

            /* handleRadioInputs(answerVal, questionOrder, questionId) {
                 this.questionIndexAnswers[questionOrder] = answerVal;
                 this.instanceQuestionOrder = questionOrder;
                 const conditions = this.subQuestionsConditions.filter(function (q) {
                     return q.related_question_order_number === questionOrder
                         && q.related_question_expected_answer === answerVal
                 });


                 if (conditions.length !== 0) {
                     this.conditionsLength = conditions.length;
                 }
                 this.questionIndex++;
                 this.updateProgressBar();
             },*/

            /*handleRadioInputs(answerVal, questionOrder, questionSubOrder, questionId) {

                const questionIndex = this.questionIndexAnswers[questionOrder] = answerVal;

                const conditions = this.subQuestionsConditions.filter(function (q) {
                    return q.related_question_order_number === questionOrder
                        && q.related_question_expected_answer === answerVal
                });

                if (conditions.length !== 0 && answerVal === 'Yes') {
                    const relatedQuestionsOrder = conditions.map(q => q.related_question_order_number);
                    const subQuestionsToShow = this.subQuestions.filter(q => q.conditions['0'].related_question_order_number === relatedQuestionsOrder[0]);

                    var subQuest = [];
                    subQuest.push(...subQuestionsToShow);
                    this.subQuestionsToShow = subQuest;
                    this.showSubQuestionNew();
                } else {
                    this.shouldShowQuestion = false;
                    this.questionIndex++;
                    this.updateProgressBar();
                }

            },*/

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

            postAnswerAndGoToNext(questionId, questionTypeAnswerId, answer) {
                axios.post('/save-answer', {
                    user_id: this.userId,
                    survey_instance_id: this.surveyInstanceId[0],
                    question_id: questionId,
                    question_type_answer_id: questionTypeAnswerId,
                    value: answer,

                })
                    .then(function (response) {
                        console.log(response);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },


        },
        mounted() {
            /* EventBus.$on('showSubQuestions', (answerVal, questionOrder, questionId, isSubQuestion) => {
                 this.handleRadioInputs(answerVal, questionOrder, questionId, isSubQuestion)
             });*/

            /* EventBus.$on('handleNumberType', () => {
                 this.handleNumberInputs();
             });*/

            EventBus.$on('handleTextType', () => {
                this.handleTextInputs();
            });

            const surveyInstanceId = this.surveydata.survey_instances.map(q => q.id);
            this.surveyInstanceId.push(...surveyInstanceId);
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

            const surveyAnswers = this.surveydata.answers;
            this.surveyAnswers.push(...surveyAnswers);
        },

    }
</script>

<style scoped>
    .questions-box {
        padding-top: 5%;
        padding-left: 9%;
    }

    .practice-title {
        font-family: Poppins;
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
        font-family: Poppins;
        font-size: 24px;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 30px;
        color: #1a1a1a;
    }

    .survey-sub-welcome-text {
        font-family: Poppins;
        font-size: 18px;
        font-weight: normal;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        text-align: center;
        margin-top: 25px;
        margin-left: 13%;
        width: 75%;
        color: #1a1a1a;
    }

    .questions-title {
        width: 83%;
        height: 100%;
        font-family: Poppins;
        font-size: 114%;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.3px;
        color: #1a1a1a;

    }

    .question-answer-type {
        width: 83%;
        height: 100%;
        font-family: Poppins;
        font-size: initial;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.3px;
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
        font-family: Poppins;
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

    .survey-container::-webkit-scrollbar {
        width: 0 !important
    }

    .scroll-buttons {
        display: flex;
        margin-left: 16%;
        margin-top: 6%;
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
        padding-left: 3%;
        position: absolute;
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
        margin-left: -25%;
        margin-top: 7%;
    }
</style>
