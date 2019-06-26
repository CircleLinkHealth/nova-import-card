<template>
    <div class="container main-container">
        <!--Survey welcome note-->
        <div class="survey-container" :class="stage === 'complete' ? 'max' : ''">
            <template v-if="stage === 'welcome'">
                <div class="card-body">
                    <div class="welcome-icon-container">
                        <img src="../../assets/images/notepad-2.png"
                             class="welcome-icon" alt="welcome icon">
                    </div>

                    <div class="survey-main-title">
                        <label id="sub-title">{{patientName}} Vitals</label>
                    </div>
                    <div class="align-items-center">
                        <div class="survey-sub-welcome-text">
                            Here is the form to fill out {{patientName}}'s Vitals. Once completed, a PPP will be
                            generated
                            for both the patient and practice, as well as a Provider Report for the doctor to evaluate.
                        </div>
                    </div>


                    <div class="btn-start-container">
                        <mdb-btn color="primary" class="btn-start" @click="startSurvey">
                            <span v-if="progress === 0">Start</span>
                            <span v-else>Continue</span>
                        </mdb-btn>
                    </div>

                    <div class="by-circlelink text-center">
                        ⚡️ by CircleLink Health
                    </div>
                </div>
            </template>
            <template v-else-if="stage === 'survey'">
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
                                :waiting="waiting"
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
                                :waiting="waiting"
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
                                :waiting="waiting"
                                v-if="question.type.type === 'multi_select'">
                            </question-type-muti-select>

                            <question-type-range
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
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
                                :waiting="waiting"
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
                                :waiting="waiting"
                                v-if="question.type.type === 'radio'">
                            </question-type-radio>

                            <question-type-date
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
                                v-if="question.type.type === 'date'">
                            </question-type-date>
                        </div>

                        <br/>

                        <div class="error" v-if="error">
                            <span v-html="error"></span>
                        </div>
                    </div>
                </div>
                <!-- add an empty div, so we can animate scroll up even if we are on last question -->
                <div style="height: 600px"></div>
            </template>
            <template v-else>
                <div class="card-body">
                    <div class="welcome-icon-container">
                        <img src="../../assets/images/doctors.png"
                             class="welcome-icon" alt="welcome icon">
                    </div>

                    <div class="survey-main-title">
                        <label>Thank You!</label>
                    </div>
                    <div class="align-items-center">
                        <div class="survey-sub-welcome-text">
                            Thank you for completing {{patientName}}'s Vitals. You can access their Vitals at any time
                            in
                            <a href="https://careplanmanager.com">CarePlanManager™</a>. A generated PDF of the PPP and
                            Provider Report is also now available in
                            that patient’s profile, and/or has been sent to your practice based on your preferences
                            (e.g., DIRECT message or e-mail).
                        </div>
                        <br/>
                        <div class="survey-sub-welcome-text">
                            If you are using the patient's phone, please hand it back now.
                        </div>
                    </div>

                    <div class="by-circlelink text-center">
                        ⚡️ by CircleLink Health
                    </div>
                </div>
            </template>

        </div>

        <!--bottom-navbar-->
        <div class="bottom-navbar container" :class="stage === 'complete' ? 'hidden' : ''">
            <!-- justify-content-end -->
            <div class="row">
                <div class="col-5 offset-2 col-sm-7 offset-sm-0 col-md-8 offset-md-0 col-lg-6 offset-lg-3 no-padding">
                    <div class="container">
                        <div class="row progress-container">
                            <div class="col-12 col-sm-12 col-md-6 text-center">
                                <span class="progress-text">
                                    {{progress}} of {{totalQuestions}} completed
                                </span>
                            </div>

                            <div class="col-12 col-sm-12 col-md-6 text-center">
                                <mdb-progress :value="progressPercentage"
                                              :height="10"/>
                            </div>

                        </div>
                    </div>
                </div>
                <!--scroll buttons-->
                <div class="col-5 col-sm-5 col-md-4 col-lg-3 no-padding">
                    <div class="row scroll-buttons">
                        <div class="col text-right">

                            <mdb-btn
                                color="primary"
                                @click="scrollDown"
                                :disabled="!canScrollDown">
                                <i class="fas fa-angle-down"></i>
                            </mdb-btn>

                            <mdb-btn
                                color="primary"
                                @click="scrollUp"
                                :disabled="!canScrollUp">
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
                stage: "welcome",
                waiting: false,
                error: null,
                questions: [],
                subQuestions: [],
                currentQuestionIndex: 0,
                latestQuestionAnsweredIndex: -1,
                progress: 0,
                totalQuestions: 0, //does not include sub-questions
                totalQuestionWithSubQuestions: 0,
                patientId: -1,
                practiceId: -1,
                patientName: '',
                surveyInstanceId: -1,
            }
        },
        computed: {
            canScrollUp() {
                return this.currentQuestionIndex > 0;
            },
            canScrollDown() {
                return this.stage === "survey"
                    && this.currentQuestionIndex < this.totalQuestionWithSubQuestions
                    && this.latestQuestionAnsweredIndex >= this.currentQuestionIndex;
            },
            progressPercentage() {
                return 100 * (this.progress) / this.totalQuestions;
            }
        },
        methods: {

            startSurvey() {
                this.stage = "survey";
            },

            scrollUp() {
                if (this.currentQuestionIndex === 0) {
                    return;
                }

                this.error = null;
                this.currentQuestionIndex = this.currentQuestionIndex - 1;
            },

            scrollDown() {
                if (this.latestQuestionAnsweredIndex < this.currentQuestionIndex) {
                    return;
                }

                this.error = null;
                this.currentQuestionIndex = this.currentQuestionIndex + 1;
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
                return `${question.pivot.order}. ${question.question_group.body}`;
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

            postAnswerAndGoToNext(questionId, questionTypeAnswerId, answer) {

                this.error = null;
                this.waiting = true;

                return axios
                    .post(`/survey/vitals/${this.practiceId}/${this.patientId}/save-answer`, {
                        patient_id: this.patientId,
                        practice_id: this.practiceId,
                        survey_instance_id: this.surveyInstanceId,
                        question_id: questionId,
                        question_type_answer_id: questionTypeAnswerId,
                        value: answer,
                    })
                    .then((response) => {
                        this.waiting = false;
                        //save the answer in state
                        const q = this.questions.find(x => x.id === questionId);

                        //increment progress only if question was not answered before
                        const incrementProgress = typeof q.answer === "undefined";
                        q.answer = {value: answer};

                        this.goToNextQuestion(incrementProgress)
                            .then(() => {
                                //NOTE
                                //this is a hack. still haven't figured out why I have to do this
                                //the next three lines of code are useless, but somehow they are needed
                                //when submitting answer, going to the next question and user clicks to go
                                //to previous question. If these lines are commented out, the `go to previous`
                                //does not work!
                                //NOTE
                                this.currentQuestionIndex = this.currentQuestionIndex + 1;
                                this.$nextTick().then(() => {
                                    this.currentQuestionIndex = this.currentQuestionIndex - 1;
                                });
                            });

                    })
                    .catch((error) => {
                        console.log(error);
                        this.waiting = false;

                        if (error.response && error.response.status === 404) {
                            this.error = "Not Found [404]";
                        }
                        else if (error.response && error.response.status === 419) {
                            this.error = "Not Authenticated [419]";
                            //reload the page which will redirect to login
                            window.location.reload();
                        }
                        else if (error.response && error.response.data) {
                            const errors = [error.response.data.message];
                            Object.keys(error.response.data.errors || []).forEach(e => {
                                errors.push(error.response.data.errors[e]);
                            });
                            this.error = errors.join('<br/>');
                        }
                        else {
                            this.error = error.message;
                        }
                    });
            },

            /**
             * If question was already answered before, we do not increment progress
             * @param incrementProgress
             */
            goToNextQuestion(incrementProgress) {

                const nextQuestion = this.questions[this.currentQuestionIndex + 1];

                //survey complete
                if (!nextQuestion) {
                    this.stage = "complete";
                    this.latestQuestionAnsweredIndex = this.currentQuestionIndex;
                    this.currentQuestionIndex = this.currentQuestionIndex + 1;
                    if (incrementProgress) {
                        this.progress = this.progress + 1;
                    }
                    return;
                }

                return new Promise(resolve => {
                    $('.survey-container').animate({
                        scrollTop: $(`#${nextQuestion.id}`).offset().top
                    }, 519, 'swing', () => {
                        this.latestQuestionAnsweredIndex = this.currentQuestionIndex;
                        this.currentQuestionIndex = this.currentQuestionIndex + 1;
                        const answered = this.questions[this.latestQuestionAnsweredIndex];

                        if (incrementProgress && answered.pivot.order !== nextQuestion.pivot.order) {
                            this.progress = this.progress + 1;
                        }

                        resolve();
                    });
                });
            },

            hasAnsweredAllOfOrder(order) {
                const questions = this.questions.filter(q => q.pivot.order === order);
                return questions.every(q => q.answer !== undefined);
            }

        },
        mounted() {
        },
        created() {
            //clone props into data
            this.patientId = this.data.id;
            this.practiceId = this.data.program_id;
            this.patientName = this.data.display_name;
            this.surveyInstanceId = this.data.survey_instances[0].id;
            this.questions = this.data.survey_instances[0].questions.slice(0);

            if (this.data.answers && this.data.answers.length) {
                let lastOrder = -1;
                this.questions.forEach(q => {
                    const a = this.surveyData.answers.find(a => a.question_id === q.id);
                    if (a) {
                        q.answer = a;
                        if (lastOrder !== q.pivot.order) {
                            this.progress = this.progress + 1;
                        }
                    }
                    lastOrder = q.pivot.order;
                });
            }

            if (typeof this.data.survey_instances[0].pivot.last_question_answered_id !== "undefined") {
                const lastQuestionAnsweredId = this.data.survey_instances[0].pivot.last_question_answered_id;
                const index = this.questions.findIndex(q => q.id === lastQuestionAnsweredId);
                this.latestQuestionAnsweredIndex = index;
                this.currentQuestionIndex = this.latestQuestionAnsweredIndex + 1;
            }

            this.totalQuestionWithSubQuestions = this.questions.length;
            this.totalQuestions = _.uniqBy(this.questions, (elem) => {
                return elem.pivot.order;
            }).length;

            if (this.data.answers && this.data.answers.length === this.questions.length) {
                this.stage = "complete";
            }
        }
    }
</script>
<style lang="scss" scoped>

</style>
