<template>
    <div class="container">
        <!--Survey welcome note-->
        <div class="survey-container">
            <template v-if="welcomeStage">
                <div class="card-body">
                    <img src="../../assets/images/notepad.png"
                         class="welcome-icon" alt="welcome icon">
                    <div class="survey-main-title">
                        <label id="sub-title">{{patientName}} Vitals</label>
                    </div>
                    <div class="align-items-center">
                        <div class="survey-sub-welcome-text">
                            Here is the form to fill out (Patient’s Name) Vitals. Once completed, a PPP will be
                            generated
                            for both the patient and practice, as well as a Provider Report for the doctor to evaluate.
                        </div>
                    </div>


                    <div class="btn-start-container">
                        <mdb-btn color="primary" class="btn-start" @click="startSurvey">Start</mdb-btn>
                    </div>

                    <div class="by-circlelink text-center">
                        ⚡️ by CircleLink Health
                    </div>
                </div>

            </template>
            <template v-else>
                <!--:data-aos="currentQuestionIndex === index"-->
                <div class="questions-box question"
                     :class="currentQuestionIndex !== index ? 'watermark' : ''"
                     v-show="index >= currentQuestionIndex"
                     v-for="(question, index) in questions">


                    <!--data-aos="fade-up"-->
                    <div class="questions-body">

                        <div class="questions-title">
                            {{question.pivot.order}}{{'.'}} {{question.body}}
                        </div>

                        <br>

                        <!--Questions Answer Type-->
                        <div class="question-answer-type">
                            <question-type-text
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :show-next-button="currentQuestionIndex === index"
                                :on-done="postAnswerAndGoToNext"
                                v-if="question.type.type === 'text'">
                            </question-type-text>

                            <question-type-checkbox
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :show-next-button="currentQuestionIndex === index"
                                :on-done="postAnswerAndGoToNext"
                                v-if="question.type.type === 'checkbox'">
                            </question-type-checkbox>

                            <question-type-muti-select
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :show-next-button="currentQuestionIndex === index"
                                :on-done="postAnswerAndGoToNext"
                                v-if="question.type.type === 'multi_select'">
                            </question-type-muti-select>

                            <question-type-range
                                :show-next-button="currentQuestionIndex === index"
                                v-if="question.type.type === 'range'">
                            </question-type-range>

                            <question-type-number
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :show-next-button="currentQuestionIndex === index"
                                :on-done="postAnswerAndGoToNext"
                                v-if="question.type.type === 'number'">
                            </question-type-number>

                            <question-type-radio
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :show-next-button="currentQuestionIndex === index"
                                :on-done="postAnswerAndGoToNext"
                                v-if="question.type.type === 'radio'">
                            </question-type-radio>

                            <question-type-date
                                :show-next-button="currentQuestionIndex === index"
                                :on-done="postAnswerAndGoToNext"
                                v-if="question.type.type === 'date'">
                            </question-type-date>
                        </div>
                    </div>
                </div>
            </template>

        </div>

        <!--bottom-navbar-->
        <div class="bottom-navbar">
            <!-- justify-content-end -->
            <div class="row">
                <!-- class="col-md-7 offset-md-2" -->
                <div class="col-md-4 offset-md-4">
                    <div class="row progress-container">
                        <div class="col-md text-center">
                            <span class="progress-text">
                                {{progress}} of {{totalQuestions}} completed
                            </span>
                            <mdb-progress :value="progressPercentage" color="warning"/>
                        </div>
                    </div>
                </div>
                <!--scroll buttons-->
                <div class="col-md-4">
                    <div class="row scroll-buttons">
                        <div class="col text-right">

                            <mdb-btn
                                color="primary"
                                @click="scrollDown"
                                :disabled="!canScrollDown"
                                style="margin-right: 20px">
                                <i class="fas fa-angle-down"></i>
                            </mdb-btn>

                            <mdb-btn
                                color="primary"
                                @click="scrollUp"
                                :disabled="!canScrollUp"
                                style="margin-right: 20px">
                                <i class="fas fa-angle-up"></i>
                            </mdb-btn>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>


<script>

    import {mdbBtn, mdbProgress} from 'mdbvue';

    import questionTypeText from "./questionTypeText";
    import questionTypeCheckbox from "./questionTypeCheckbox";
    import questionTypeRange from "./questionTypeRange";
    import questionTypeNumber from "./questionTypeNumber";
    import questionTypeRadio from "./questionTypeRadio";
    import questionTypeDate from "./questionTypeDate";
    import questionTypeMultiSelect from "./questionTypeMultiSelect";

    export default {
        props: ['data'],

        components: {
            'mdb-btn': mdbBtn,
            'mdb-progress': mdbProgress,
            'question-type-text': questionTypeText,
            'question-type-checkbox': questionTypeCheckbox,
            'question-type-range': questionTypeRange,
            'question-type-number': questionTypeNumber,
            'question-type-radio': questionTypeRadio,
            'question-type-date': questionTypeDate,
            'question-type-muti-select': questionTypeMultiSelect
        },

        data() {
            return {
                welcomeStage: true,
                questions: [],
                subQuestions: [],
                currentQuestionIndex: 0,
                latestQuestionAnsweredIndex: -1,
                progress: 0,
                totalQuestions: 0, //does not include sub-questions
                patientId: -1,
                patientName: '',
                surveyInstanceId: -1,
            }
        },
        computed: {
            canScrollUp() {
                return this.currentQuestionIndex > 0;
            },
            canScrollDown() {
                return !this.welcomeStage
                    && this.currentQuestionIndex < this.totalQuestions
                    && this.latestQuestionAnsweredIndex >= this.currentQuestionIndex;
            },
            progressPercentage() {
                return 100 * this.latestQuestionAnsweredIndex / this.totalQuestions;
            }
        },
        methods: {

            startSurvey() {
                this.welcomeStage = false;
                this.currentQuestionIndex = 0;
            },

            scrollUp() {
                if (this.currentQuestionIndex === 0) {
                    return;
                }
                this.currentQuestionIndex = this.currentQuestionIndex - 1;
            },

            scrollDown() {
                if (this.latestQuestionAnsweredIndex < this.currentQuestionIndex) {
                    return;
                }
                this.currentQuestionIndex = this.currentQuestionIndex + 1;
            },

            postAnswerAndGoToNext(answer) {

                return new Promise((resolve, reject) => {
                    this.goToNextQuestion();
                    resolve();
                });

                /*
                var answerData = JSON.stringify(answer);

                axios.post('/save-answer', {
                    user_id: this.userId,
                    survey_instance_id: this.surveyInstanceId[0],
                    question_id: this.question.id,
                    question_type_answer_id: this.questionTypeAnswerId,
                    value: answerData,
                })
                    .then(function (response) {
                        console.log(response);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                */
            },

            goToNextQuestion() {
                this.latestQuestionAnsweredIndex = this.currentQuestionIndex;
                this.currentQuestionIndex = this.currentQuestionIndex + 1;
                const answered = this.questions[this.latestQuestionAnsweredIndex];

                //increment progress only if current question is not a sub question
                if (answered.pivot.sub_order === null) {
                    this.progress = this.progress + 1;
                }
            }

        },
        mounted() {

        },
        created() {
            //clone props into data
            this.patientId = this.data.id;
            this.patientName = this.data.display_name;
            this.surveyInstanceId = this.data.survey_instances[0].id;
            this.questions = this.data.survey_instances[0].questions.slice(0);
            this.totalQuestions = _.uniqBy(this.questions, (elem) => {
                return elem.pivot.order;
            }).length;
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
        text-align: left;
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
        font-family: Poppins, serif;
        font-size: initial;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.3px;
        color: #1a1a1a;
    }

    .btn-start-container {
        text-align: center;
    }

    .btn-start {
        margin-top: 60px;
        margin-bottom: 20px;
        width: 180px;
        height: 60px;
        font-family: Poppins, serif;
        font-size: 18px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        text-align: center;
    }

    .btn-primary {
        font-size: 18px;
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .bottom-navbar {
        background-color: #ffffff;
        border-bottom: 1px solid #808080;
        border-left: 1px solid #808080;
        border-right: 1px solid #808080;
        height: 100px;
    }

    .by-circlelink {
        font-family: Poppins, serif;
        font-size: 18px;
        font-weight: 600;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        color: #50b2e2;
    }

    .survey-container {
        margin-top: 10px;
        background-color: #f2f6f9;
        border-top: 1px solid #808080;
        border-left: 1px solid #808080;
        border-right: 1px solid #808080;
        width: 100%;
        height: 600px;
        overflow-y: scroll;
    }

    .survey-container::-webkit-scrollbar {
        width: 0 !important
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

    .scroll-buttons .btn {
        margin-top: 20px;
        width: 60px;
        height: 60px;
    }

    .scroll-buttons .btn:last-child {
        margin-right: 60px;
    }

    .scroll-buttons .fas {
        width: 12px;
        height: 20px;
    }

    .progress-container {
        margin-top: 36px;
    }

    .progress-text {
        font-family: Poppins, serif;
        font-size: 18px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1px;
        text-align: right;
        color: #1a1a1a;
    }

    .progress-bar {
        width: 100%;
        height: 10px;
        opacity: 0.5;
        border-radius: 5px;
        border: solid 1px #4aa5d2;
        background-color: #50b2e2;
    }

    .watermark {
        opacity: 0.1;
    }

</style>
