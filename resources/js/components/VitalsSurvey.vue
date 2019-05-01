<template>
    <div class="container">
        <!--Survey welcome note-->
        <div class="survey-container">
            <template v-if="welcomeStage">
                <div class="card-body">
                    <div class="welcome-icon-container">
                        <img src="../../assets/images/notepad.png"
                             class="welcome-icon" alt="welcome icon">
                    </div>

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
                <div class="questions-box question"
                     :id="question.id"
                     :class="currentQuestionIndex !== index ? 'watermark' : 'active'"
                     v-show="index >= currentQuestionIndex"
                     v-for="(question, index) in questions">

                    <div class="questions-body">

                        <div v-if="isSubQuestion(question) && shouldShowQuestionGroupTitle(question)"
                             class="questions-title">
                            {{getQuestionGroupTitle(question)}}
                        </div>

                        <br>

                        <div class="questions-title">
                            {{getQuestionTitle(question)}}
                        </div>

                        <br>

                        <!--Questions Answer Type-->
                        <div class="question-answer-type">
                            <question-type-text
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                v-if="question.type.type === 'text'">
                            </question-type-text>

                            <question-type-checkbox
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                v-if="question.type.type === 'checkbox'">
                            </question-type-checkbox>

                            <question-type-muti-select
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                v-if="question.type.type === 'multi_select'">
                            </question-type-muti-select>

                            <question-type-range
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                v-if="question.type.type === 'range'">
                            </question-type-range>

                            <question-type-number
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                v-if="question.type.type === 'number'">
                            </question-type-number>

                            <question-type-radio
                                :question="question"
                                :userId="patientId"
                                :surveyInstanceId="surveyInstanceId"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :style-horizontal="true"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                v-if="question.type.type === 'radio'">
                            </question-type-radio>

                            <question-type-date
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                v-if="question.type.type === 'date'">
                            </question-type-date>
                        </div>
                    </div>
                </div>
                <!-- add an empty div, so we can animate scroll up even if we are on last question -->
                <div style="height: 600px"></div>
            </template>

        </div>

        <!--bottom-navbar-->
        <div class="bottom-navbar container">
            <!-- justify-content-end -->
            <div class="row">
                <div class="col-6 col-sm-7 col-md-8 offset-md-0 col-lg-6 offset-lg-3">
                    <div class="row progress-container">
                        <div class="col-md text-center progress-flex-container">
                            <span class="progress-text">
                                {{progress}} of {{totalQuestions}} completed
                            </span>
                            <mdb-progress :value="progressPercentage"
                                          :height="10"/>
                        </div>
                    </div>
                </div>
                <!--scroll buttons-->
                <div class="col-6 col-sm-5 col-md-4 col-lg-3">
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
                return true;
                /*
                return !this.welcomeStage
                    && this.currentQuestionIndex < this.totalQuestions
                    && this.latestQuestionAnsweredIndex >= this.currentQuestionIndex;
                    */
            },
            progressPercentage() {
                return 100 * (this.latestQuestionAnsweredIndex + 1) / this.totalQuestions;
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

                this.currentQuestionIndex = this.currentQuestionIndex + 1;
                /*
                if (this.latestQuestionAnsweredIndex < this.currentQuestionIndex) {
                    return;
                }
                this.currentQuestionIndex = this.currentQuestionIndex + 1;
                */
            },

            isSubQuestion(question) {
                return question.question_group !== null;
            },

            isLastQuestion(question) {
                return this.questions[this.questions.length - 1].id === question.id;
            },

            shouldShowQuestionGroupTitle(question) {
                return question.pivot.sub_order != null && (question.pivot.sub_order === "a" || question.pivot.sub_order === "1");
            },

            getQuestionGroupTitle(question) {
                return question.question_group.body;
            },

            getQuestionTitle(question) {

                if (this.isSubQuestion(question)) {
                    return `${question.pivot.sub_order}. ${question.body}`;
                }

                return `${question.pivot.order}. ${question.body}`;
            },

            /**
             * For components that need to access other questions.
             * i.e. questions that are shown only if some conditions are met,
             *      or if their answers are auto generated from previous answers
             */
            getAllQuestions() {
                return this.questions;
            },

            postAnswerAndGoToNext(questionId, answer) {

                return new Promise((resolve, reject) => {

                    //save the answer in state
                    const q = this.questions.find(x => x.id === questionId);
                    q.answer = answer;

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

                const nextQuestion = this.questions[this.currentQuestionIndex + 1];

                //survey complete
                if (!nextQuestion) {
                    return;
                }

                $('.survey-container').animate({
                    scrollTop: $(`#${nextQuestion.id}`).offset().top
                }, 500, 'swing', () => {
                    this.latestQuestionAnsweredIndex = this.currentQuestionIndex;
                    this.currentQuestionIndex = this.currentQuestionIndex + 1;
                    const answered = this.questions[this.latestQuestionAnsweredIndex];

                    //increment progress only if current question is not a sub question
                    if (answered.pivot.sub_order === null) {
                        this.progress = this.progress + 1;
                    } else {
                        //if this is the last sub question of a group, increment progress

                        //get all sub questions and sort them (i.e ["a", "b", "c"]
                        const allSubs = this.questions.filter(q => q.pivot.order === answered.pivot.order).map(q => q.pivot.sub_order).sort();
                        if (allSubs[allSubs.length - 1] === answered.pivot.sub_order) {
                            this.progress = this.progress + 1;
                        }
                    }
                });
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
<style lang="scss">

    $primary-color: #50b2e2;

    .navbar-laravel {
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }

    .next-btn {
        font-size: 18px;
        font-family: Poppins, serif;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        padding: 6px 40px;
        line-height: normal;
        letter-spacing: 1px;
        color: #ffffff;
        text-transform: none;
        height: 40px;
        border-radius: 5px;
        border: solid 1px #4aa5d2;
        background-color: $primary-color;
        margin: 0;
    }

    .questions-box {
        padding-top: 5%;
        padding-left: 9%;
    }

    .practice-title {
        font-family: Poppins, serif;
        font-size: 18px;
        letter-spacing: 1.5px;
        text-align: center;
        margin-top: 20px;
        color: $primary-color;
    }

    .practice-title .text-style-1 {
        font-weight: 600;
    }

    .survey-main-title {
        font-family: Poppins, serif;
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
        color: $primary-color;
    }

    .survey-container {
        margin-top: 10px;
        background-color: #f2f6f9;
        border-top: 1px solid #808080;
        border-left: 1px solid #808080;
        border-right: 1px solid #808080;
        width: 100%;
        height: 600px;
        overflow-y: hidden;
    }

    .survey-container::-webkit-scrollbar {
        width: 0 !important
    }

    .welcome-icon-container {
        margin: auto;
        text-align: center;
    }

    .welcome-icon {
        width: 108px;
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
        padding: 0;
        margin-top: 20px;
        width: 60px;
        height: 60px;
    }

    .scroll-buttons .btn:last-child {
        margin-right: 60px;
    }

    .scroll-buttons .fas {
        font-size: 30px;
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
        white-space: nowrap;
    }

    .progress {
        width: 100%;
        margin-left: 1%;
        margin-top: 9px;
        height: 10px;
        border-radius: 5px;
        border: solid 1px #d2e8f3;
        background-color: #a7d9f1;
    }

    .progress-bar {
        background-color: $primary-color !important;
    }

    .progress-flex-container {
        display: flex;
        justify-content: center;
        flex-wrap: nowrap;
    }

    .watermark {
        opacity: 0.1;
    }

</style>
