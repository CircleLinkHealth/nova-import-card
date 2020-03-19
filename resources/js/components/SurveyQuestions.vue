<template>
    <div class="container main-container" :class="stage">

        <div class="top-buttons" v-if="adminMode">
            <mdb-row class="no-gutters">
                <mdb-col>
                    <div class="top-left-fixed">
                        <mdb-btn class="btn-toggle-edit" color="primary" @click="goBack">
                            <mdb-icon icon="chevron-circle-left" size="3x"/>
                        </mdb-btn>
                    </div>
                    <!-- shown on mobiles -->
                    <mdb-btn flat darkWaves @click="goBack" class="hidden mobile-view">
                        <mdb-icon icon="chevron-circle-left"/>
                        Back
                    </mdb-btn>
                </mdb-col>
                <mdb-col>
                    <div class="top-right-fixed">
                        <mdb-btn class="btn-toggle-edit" :outline="readOnlyMode ? 'info' : 'danger'"
                                 @click="toggleReadOnlyMode">
                            <mdb-icon :icon="readOnlyMode ? 'pencil-alt' : 'eye'" size="2x"/>
                        </mdb-btn>
                    </div>
                    <!-- shown on mobiles -->
                    <mdb-btn flat darkWaves @click="toggleReadOnlyMode" class="hidden mobile-view">
                        <mdb-icon :icon="readOnlyMode ? 'pencil-alt' : 'eye'"/>
                        {{readOnlyMode ? 'Edit' : 'View'}}
                    </mdb-btn>
                </mdb-col>
            </mdb-row>
        </div>

        <!--Survey welcome note-->
        <div class="survey-container"
             :class="{ max: stage === 'complete', 'read-only': readOnlyMode, 'with-top-buttons': adminMode && stage !== 'welcome' }">
            <template v-if="stage === 'welcome'">
                <div v-show="isHra" class="practice-title">
                    <label id="title">
                        <strong>{{practiceName}}</strong>
                        <br/>
                        Dr. {{doctorsLastName}}’s Office
                    </label>
                </div>
                <div class="card-body">
                    <img :src="welcomeIcon"
                         class="welcome-icon" alt="welcome icon">
                    <div class="survey-main-title">
                        <label id="sub-title">{{welcomeTitle}}</label>
                    </div>
                    <div v-if="isHra" class="survey-sub-welcome-text">Welcome to your
                        Annual Wellness Visit (AWV) Questionnaire! Understanding your health is of upmost importance to
                        us,
                        so thank you for taking time to fill this out.
                        <br/>
                        <br/>
                        If there’s any question you have trouble answering, feel free to click the call button on the
                        bottom
                        left and a representative will help when you call the number. If you skip any questions, our
                        reps
                        will also reach out shortly. Thanks!
                    </div>
                    <div v-else class="survey-sub-welcome-text">
                        Here is the form to fill out {{patientName}}'s Vitals. Once completed, a PPP will be
                        generated
                        for both the patient and practice, as well as a Provider Report for the doctor to evaluate.
                    </div>

                    <div class="btn-start-container">
                        <!-- @todo: this is not working exactly as expected so im keepin one element true and i ll get back-->
                        <mdb-btn
                            color="primary" class="btn-start" @click="showQuestions">
                            <span v-if="progress === 0">Start</span>
                            <span v-else>Continue</span>
                        </mdb-btn>
                    </div>
                    <div class="by-circlelink">
                        ⚡️ by CircleLink Health
                    </div>
                </div>
            </template>

            <!--Questions-->
            <template v-else-if="stage === 'survey'">
                <div class="questions-box question"
                     :id="question.id"
                     :class="!readOnlyMode && currentQuestionIndex !== index ? (question.conditions && question.conditions.length > 0 ? 'non-visible' : 'watermark') : 'active'"
                     v-show="readOnlyMode || index >= currentQuestionIndex"
                     v-for="(question, index) in questions">
                    <div class="questions-body">

                        <div v-if="isSubQuestion(question) && shouldShowQuestionGroupTitle(question)"
                             class="questions-title">
                            <span v-html="getQuestionGroupTitle(question)"></span>
                        </div>

                        <br v-if="shouldShowQuestionGroupTitle(question)">

                        <div class="questions-title margin-bottom-10">
                            {{getQuestionTitle(question)}}
                        </div>

                        <!--Questions Answer Type-->
                        <div class="question-answer-type">
                            <question-type-text
                                :question="question"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
                                :read-only="readOnlyMode"
                                v-if="question.type.type === 'text'">
                            </question-type-text>

                            <question-type-checkbox
                                :question="question"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
                                :read-only="readOnlyMode"
                                v-if="question.type.type === 'checkbox'">
                            </question-type-checkbox>

                            <question-type-muti-select
                                :question="question"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
                                :read-only="readOnlyMode"
                                v-if="question.type.type === 'multi_select'">
                            </question-type-muti-select>

                            <question-type-range
                                :read-only="readOnlyMode"
                                v-if="question.type.type === 'range'">
                            </question-type-range>

                            <question-type-number
                                :question="question"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
                                :read-only="readOnlyMode"
                                v-if="question.type.type === 'number'">
                            </question-type-number>

                            <question-type-radio
                                :question="question"
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :style-horizontal="false"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
                                :read-only="readOnlyMode"
                                v-if="question.type.type === 'radio'">
                            </question-type-radio>

                            <question-type-date
                                :read-only="readOnlyMode"
                                v-if="question.type.type === 'date'">
                            </question-type-date>
                        </div>
                    </div>
                    <div class="error" v-if="error">
                        <span v-html="error"></span>
                    </div>

                    <div v-if="!readOnlyMode && currentQuestionIndex === index" class="question-scroll-container"
                         style="display: none;">
                        <div>Scroll</div>
                        <mdb-btn color="primary" class="question-scroll" @click="scrollQuestionToBottom">
                            &nbsp;
                        </mdb-btn>
                    </div>

                </div>
                <!-- add an empty div, so we can animate scroll up even if we are on last question -->
                <div v-if="!readOnlyMode" style="height: 600px"></div>
            </template>

            <!-- Survey Completed - should only be shown in Vitals Survey -->
            <template v-else>
                <div class="card-body">
                    <div class="welcome-icon-container">
                        <img src="../../images/doctors.png"
                             class="welcome-icon" alt="welcome icon">
                    </div>

                    <div class="survey-main-title">
                        <label>Thank You!</label>
                    </div>
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
                        If you are using the patient's phone, please <strong>logout</strong> and hand it back now.
                    </div>

                    <div class="btn-start-container">
                        <mdb-btn color="primary" class="btn-start" @click="logout">
                            Logout
                        </mdb-btn>

                        <form id="logout-form" action="/logout" method="POST" style="display: none;">
                        </form>
                    </div>

                    <div class="by-circlelink">
                        ⚡️ by CircleLink Health
                    </div>
                </div>
            </template>

        </div>
        <div class="call-assistance">
            <call-assistance v-if="/*practiceOutgoingPhoneNumber && */callAssistance"
                             :phone-number="practiceOutgoingPhoneNumber"
                             :from-number="clhPhoneNumber"
                             :cpm-caller-token="cpmCallerToken"
                             :cpm-caller-url="cpmCallerUrl"
                             :debug="debug"
                             @closeCallAssistanceModal="toggleCallAssistance">
            </call-assistance>
        </div>

        <div class="bottom-navbar container no-padding"
             :class="(stage === 'complete' || stage === 'welcome') ? 'hidden' : ''">
            <!-- justify-content-end -->
            <div class="row no-gutters">
                <div class="col-3 col-sm-4 col-md-3 col-lg-2 text-center">
                    <div class="container">
                        <div class="row no-gutters scroll-buttons" v-show="!readOnlyMode">
                            <mdb-btn color="primary" @click="toggleCallAssistance" class="call-btn-round">
                                <mdb-icon :icon="callAssistance ? 'times' : 'phone-alt'"
                                          style="font-size: 1.5em !important;">
                                </mdb-icon>
                            </mdb-btn>
                        </div>
                    </div>
                </div>
                <div :class="readOnlyMode ? 'col-6' : 'col-5'"
                     class="col-sm-4 offset-sm-0 col-md-4 offset-md-0 col-lg-6 offset-lg-1">
                    <div class="container">
                        <div class="row no-gutters progress-container">
                            <div class="col-12 col-sm-12 col-md-6 offset-md-1 col-lg-6 offset-lg-0 text-center">
                                <span class="progress-text">
                                    {{progress}} of {{totalQuestions}} completed
                                </span>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 offset-md-1 col-lg-4 offset-lg-0 text-center">
                                <mdb-progress :value="progressPercentage"
                                              :height="10"/>
                            </div>

                        </div>
                    </div>
                </div>
                <!--scroll buttons-->
                <div class="col-4 col-sm-4 col-md-4 offset-md-1 col-lg-3 offset-lg-0" v-show="!readOnlyMode">
                    <div class="container">
                        <div class="row no-gutters scroll-buttons">
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
    </div>
</template>


<script>
    import hraWelcomeIcon from '../../images/notepad.png';
    import vitalsWelcomeIcon from '../../images/notepad.png';
    import {mdbBtn, mdbCol, mdbIcon, mdbProgress, mdbRow} from 'mdbvue';
    import questionTypeText from "./questionTypeText";
    import questionTypeCheckbox from "./questionTypeCheckbox";
    import questionTypeRange from "./questionTypeRange";
    import questionTypeNumber from "./questionTypeNumber";
    import questionTypeRadio from "./questionTypeRadio";
    import questionTypeDate from "./questionTypeDate";
    import callAssistance from "./callAssistance";
    import questionTypeMultiSelect from "./questionTypeMultiSelect";
    import $ from "jquery";

    export default {
        props: ['surveyName', 'surveyData', 'adminMode', 'cpmCallerUrl', 'cpmCallerToken', 'clhPhoneNumber', 'debug'],

        components: {
            mdbIcon,
            mdbRow,
            mdbCol,
            'mdb-btn': mdbBtn,
            'mdb-progress': mdbProgress,
            'question-type-text': questionTypeText,
            'question-type-checkbox': questionTypeCheckbox,
            'question-type-range': questionTypeRange,
            'question-type-number': questionTypeNumber,
            'question-type-radio': questionTypeRadio,
            'question-type-date': questionTypeDate,
            'call-assistance': callAssistance,
            'question-type-muti-select': questionTypeMultiSelect,
        },

        data() {

            const patientName = this.surveyData.display_name;
            const welcomeIcon = this.surveyName === 'hra' ? hraWelcomeIcon : vitalsWelcomeIcon;
            const welcomeTitle = this.surveyName === 'hra' ? 'Annual Wellness Visit (AWV) Questionnaire' : `${patientName} Vitals`;

            return {
                welcomeIcon,
                welcomeTitle,
                stage: "welcome",
                actionsDisabled: false, //to prevent double-clicking
                questionsStage: false,
                welcomeStage: true,
                callAssistance: false,
                questions: [],
                subQuestions: [],
                instanceQuestionOrder: -1,
                shouldShowQuestion: false,
                questionIndex: 0,
                progressCount: 0,
                userId: this.surveyData.id,
                surveyInstanceId: this.surveyData.survey_instances[0].id,
                questionIndexAnswers: [],
                conditionsLength: 0,
                latestQuestionAnsweredIndex: -1,
                currentQuestionIndex: 0,
                error: null,
                progress: 0,
                waiting: false,
                practiceId: this.surveyData.primary_practice.id,
                practiceName: this.surveyData.primary_practice.display_name,
                patientName,
                practiceOutgoingPhoneNumber: this.surveyData.primary_practice.outgoing_phone_number,
                doctorsLastName: this.surveyData.billing_provider && this.surveyData.billing_provider.length ? this.surveyData.billing_provider[0].user.last_name : '???',
                totalQuestions: 0,
                totalQuestionWithSubQuestions: 0,
                readOnlyMode: false,
            }
        },

        computed: {

            isHra() {
                return this.surveyName === 'hra';
            },

            subQuestionsConditions() {
                return this.subQuestions.flatMap(function (subQuestion) {
                    return subQuestion.conditions;
                });
            },

            lastQuestionAnswered() {
                return this.surveyData.survey_instances[0].pivot.last_question_answered_id;
            },

            questionsOrder() {
                return this.questions.flatMap(function (q) {
                    return q.pivot.order + q.pivot.sub_order;
                });
            },

            canScrollUp() {
                return (this.stage === "survey" || this.stage === "complete")
                    && this.currentQuestionIndex > 0;
            },
            canScrollDown() {

                const canProceed = this.stage === "survey"
                    && this.currentQuestionIndex < this.questions.length;

                let nextHasAnswer = false;
                if (canProceed) {
                    const nextQuestion = this.getNextQuestion(this.currentQuestionIndex);
                    nextHasAnswer = nextQuestion != null &&
                        nextQuestion.question != null &&
                        typeof nextQuestion.question.answer !== "undefined" &&
                        !(nextQuestion.question.answer.value === null || typeof nextQuestion.question.answer.value === "undefined");
                }

                return nextHasAnswer;
            },
            progressPercentage() {
                return 100 * (this.progress) / this.totalQuestions;
            }

        },

        methods: {

            getPatientsListUrl() {
                return '/manage-patients';
            },

            getVitalsWelcomeUrl() {
                return `/survey/vitals/${this.userId}/welcome`;
            },

            showQuestions() {
                this.stage = "survey";
            },

            scrollUp() {
                if (this.currentQuestionIndex === 0 || this.actionsDisabled) {
                    return;
                }

                this.actionsDisabled = true;

                this.error = null;

                const prevQuestionIndex = this.getPreviousQuestionIndex(this.currentQuestionIndex);
                this.scrollToQuestion(this.questions[prevQuestionIndex].id)
                    .then(() => {
                        this.currentQuestionIndex = prevQuestionIndex;
                        this.actionsDisabled = false;
                    });
            },

            scrollDown() {
                if ((this.questions.length <= this.currentQuestionIndex) || this.actionsDisabled) {
                    return;
                }

                this.actionsDisabled = true;

                this.error = null;

                const nextQuestionIndex = this.getNextQuestionIndex(this.currentQuestionIndex);
                this.scrollToQuestion(this.questions[nextQuestionIndex].id)
                    .then(() => {
                        this.currentQuestionIndex = nextQuestionIndex;
                        this.actionsDisabled = false;
                    });

            },

            isSubQuestion(question) {
                return question.pivot.sub_order !== null;
            },

            isLastQuestion(question) {
                return this.questions[this.questions.length - 1].id === question.id;
            },

            shouldShowQuestionGroupTitle(question) {
                return question.question_group_id && question.pivot.sub_order != null && (question.pivot.sub_order === "a" || question.pivot.sub_order === "1" || question.pivot.sub_order === "1.");
            },

            hasQuestionGroupTitle(question) {
                return question.question_group_id && question.pivot.sub_order != null;
            },

            getQuestionGroupTitle(question) {
                return `${question.pivot.order}. ${question.question_group.body}`;
            },

            getQuestionTitle(question) {
                if (this.hasQuestionGroupTitle(question)) {
                    let str = `${question.pivot.sub_order}.`;
                    str = str.replace('..', '.'); //make sure we don't end up with two `..`
                    return `${str} ${question.body}`;
                }

                if (this.isSubQuestion(question)) {
                    let str = `${question.pivot.order}${question.pivot.sub_order}.`;
                    str = str.replace('..', '.');
                    return `${str} ${question.body}`;
                }

                return `${question.pivot.order}. ${question.body}`;
            },

            getAllQuestions() {
                return this.questions;
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


            postAnswerAndGoToNext(questionId, questionTypeAnswerId, answer, isLastQuestion) {

                if (this.actionsDisabled) {
                    return;
                }

                this.actionsDisabled = true;
                this.error = null;
                this.waiting = true;

                axios.post(`/survey/${this.surveyName}/${this.userId}/save-answer`, {
                    patient_id: this.userId,
                    practice_id: this.practiceId,
                    survey_instance_id: this.surveyInstanceId,
                    question_id: questionId,
                    question_type_answer_id: questionTypeAnswerId,
                    value: answer,
                    survey_complete: isLastQuestion
                })
                    .then((response) => {

                        const surveyStatus = response.data ? response.data.survey_status : "in_progress";
                        const nextQuestionId = response.data ? response.data.next_question_id : null;

                        this.waiting = false;
                        //save the answer in state
                        const q = this.questions.find(x => x.id === questionId);

                        //increment progress only if question was not answered before
                        const incrementProgress = typeof q.answer === "undefined" || (typeof q.answer.value === "undefined" || q.answer.value === null);
                        q.answer = {value: answer};
                        if (!this.surveyData.answers) {
                            this.surveyData.answers = [];
                        }
                        const currentAnswer = this.surveyData.answers.find(a => a.question_id === questionId);
                        if (currentAnswer) {
                            currentAnswer.value = answer;
                        } else {
                            this.surveyData.answers.push({question_id: questionId, value: {value: answer}});
                        }

                        if (this.isHra && isLastQuestion && surveyStatus === "completed") {
                            window.location.href = this.getVitalsWelcomeUrl();
                            return;
                        }

                        //cover the case where survey was completed, but an answer was changed
                        //and now a question that was not shown before needs to be answered
                        //or the opposite
                        if (nextQuestionId && this.progress === this.totalQuestions) {
                            this.calculateSurveyProgress();
                            if (this.progress !== this.totalQuestions) {
                                let nextQuestionIndex = -1;
                                const nextQuestion = this.questions.find((q, index) => {
                                    if (q.id === nextQuestionId) {
                                        nextQuestionIndex = index;
                                        return true;
                                    }
                                    return false;
                                });
                                nextQuestion.disabled = false;

                                const next = this.getNextQuestion(nextQuestionIndex - 1);
                                if (next) {
                                    this.currentQuestionIndex = next.index;
                                }
                            }
                        } else if (nextQuestionId === null && this.progress !== this.totalQuestions) {
                            this.calculateSurveyProgress();
                        }

                        this.goToNextQuestion(incrementProgress)
                            .then(() => {
                                //NOTE
                                //this is a hack. still haven't figured out why I have to do this
                                //the next three lines of code are useless, but somehow they are needed
                                //when submitting answer, going to the next question and user clicks to go
                                //to previous question. If these lines are commented out, the `go to previous`
                                //does not work!
                                //NOTE
                                this.currentQuestionIndex = this.currentQuestionIndex - 1;
                                this.$nextTick().then(() => {
                                    this.currentQuestionIndex = this.currentQuestionIndex + 1;
                                    this.actionsDisabled = false;
                                });
                            });

                    })
                    .catch((error) => {
                        console.log(error);
                        this.actionsDisabled = false;
                        this.waiting = false;

                        if (error.response && error.response.status === 404) {
                            this.error = "Not Found [404]";
                        } else if (error.response && error.response.status === 419) {
                            this.error = "Not Authenticated [419]";
                            //reload the page which will redirect to login
                            window.location.reload();
                        } else if (error.response && error.response.data) {
                            const errors = [error.response.data.message];
                            Object.keys(error.response.data.errors || []).forEach(e => {
                                errors.push(error.response.data.errors[e]);
                            });
                            this.error = errors.join('<br/>');
                        } else {
                            this.error = error.message;
                        }
                    });
            },

            getPreviousQuestionIndex(index) {
                const newIndex = index - 1;
                const prevQuestion = this.questions[newIndex];
                if (!prevQuestion) {
                    return 0;
                }

                if (prevQuestion.disabled) {
                    return this.getPreviousQuestionIndex(index - 1);
                }

                //if we reach here, it means we have not faced this question yet in this session
                //it might still be disabled though -> think completing questions then refreshing the page
                //need to check if there are certain conditions that have to be met before showing this question
                let canGoToPrev = true;
                if (prevQuestion.conditions && prevQuestion.conditions.length) {
                    for (let i = 0; i < prevQuestion.conditions.length; i++) {
                        const q = prevQuestion.conditions;
                        const prevQuestConditions = q[i];

                        //we are evaluating only the first condition.related_question_order_number
                        //For now is OK since we are depending only on ONE related Question
                        const questions = this.getQuestionsOfOrder(prevQuestConditions.related_question_order_number);
                        const firstQuestion = questions[0];
                        if (!firstQuestion.answer || !firstQuestion.answer.value) {
                            canGoToPrev = false;
                            break;
                        }

                        //If conditions needs to be compared against to "gte" or "lte"
                        if (prevQuestConditions.hasOwnProperty('operator')) {
                            if (prevQuestConditions.operator === 'greater_or_equal_than') {
                                //Again we use only the first Question of the related Questions, which is OK for now.
                                if (firstQuestion.answer.value.value >= prevQuestConditions.related_question_expected_answer) {
                                    canGoToPrev = false;
                                    break;
                                }
                                canGoToPrev = true;
                                break;
                            }

                            if (prevQuestConditions.operator === 'less_or_equal_than') {
                                if (firstQuestion.answer.value.value <= prevQuestConditions.related_question_expected_answer) {
                                    canGoToPrev = false;
                                    break;
                                }
                                canGoToPrev = true;
                                break;
                            }
                        }
                        //default comparison
                        const expectedAnswersEqualsValue = q.map(q => q.related_question_expected_answer === firstQuestion.answer.value.value);

                        if (!expectedAnswersEqualsValue.includes(true)) {
                            canGoToPrev = false;
                            break;
                        }
                        //if no expected answer, we look for any answer, if any
                        else if (typeof q.related_question_expected_answer === "undefined") {
                            if (Array.isArray(firstQuestion.answer.value) && firstQuestion.answer.value.length === 0) {
                                canGoToPrev = false;
                            } else if (typeof firstQuestion.answer.value === "string" && firstQuestion.answer.value.length === 0) {
                                canGoToPrev = false;
                            } else if (firstQuestion.answer.value.value && firstQuestion.answer.value.value.length === 0) {
                                canGoToPrev = false;
                            }

                            if (!canGoToPrev) {
                                break;
                            }
                        }
                    }
                }
                return canGoToPrev ? newIndex : this.getPreviousQuestionIndex(index - 1);
            },

            hasAnswerAheadOfQuestion(index) {
                const next = this.getNextQuestion(index);
                if (!next) {
                    return false;
                }
                const answers = this.surveyData.answers;
                const a = answers.find(a => a.question_id === next.question.id);
                return a && a.value;
            },

            /**
             * Check if a question has been answered from another question's conditions
             * i.e. q32 -> conditions -> q2 => return true if q2 has been answered
             * Will be used to check whether a question (q32)
             * has not been answered because its not shown
             * or whether user hasn't reached there.
             * Needed for the survey progress bar.
             *
             * @param index
             * @return boolean
             */
            isQuestionAnsweredFromQuestionConditions(index) {
                const q = this.questions[index];
                const conditions = q.conditions;
                if (!conditions || conditions.length === 0) {
                    return true;
                }

                // we are evaluating only the first condition.related_question_order_number
                const condition = conditions[0];

                //For now is OK since we are depending only on ONE related Question
                const questions = this.getQuestionsOfOrder(condition.related_question_order_number);
                const question = questions[0];
                return !!question.answer;
            },

            getNextQuestionIndex(index) {
                const newIndex = index + 1;
                const nextQuestion = this.questions[newIndex];
                if (!nextQuestion) {
                    return (this.questions.length - 1);
                }

                //if we reach here, it means we have not faced this question yet in this session
                //it might still be disabled though -> think completing questions then refreshing the page
                //need to check if there are certain conditions that have to be met before showing this question
                let canGoToNext = true;
                const allQuestionConditions = nextQuestion.conditions || [];
                for (let i = 0; i < allQuestionConditions.length; i++) {
                    const nextQuestConditions = allQuestionConditions[i];
                    //we are evaluating only the first condition.related_question_order_number
                    //For now is OK since we are depending only on ONE related Question
                    const questions = this.getQuestionsOfOrder(nextQuestConditions.related_question_order_number);
                    const firstQuestion = questions[0];
                    if (!firstQuestion.answer || !firstQuestion.answer.value) {
                        canGoToNext = false;
                        break;
                    }

                    //If conditions needs to be compared against to "gte" or "lte"
                    if (nextQuestConditions.hasOwnProperty('operator')) {
                        const answerValue = Array.isArray(firstQuestion.answer.value.value) ? firstQuestion.answer.value.value[0] : firstQuestion.answer.value.value;

                        if (nextQuestConditions.operator === 'greater_or_equal_than') {
                            //Again we use only the first Question of the related Questions, which is OK for now.
                            canGoToNext = answerValue >= nextQuestConditions.related_question_expected_answer;
                            break;
                        }

                        if (nextQuestConditions.operator === 'less_or_equal_than') {
                            canGoToNext = answerValue <= nextQuestConditions.related_question_expected_answer;
                            break;
                        }
                    }
                    //default comparison
                    const expectedAnswersEqualsValue = allQuestionConditions.map(q => q.related_question_expected_answer === firstQuestion.answer.value.value);

                    if (!expectedAnswersEqualsValue.includes(true)) {
                        canGoToNext = false;
                        break;
                    }
                    //if no expected answer, we look for any answer, if any
                    else if (typeof nextQuestConditions.related_question_expected_answer === "undefined") {
                        if (Array.isArray(firstQuestion.answer.value) && firstQuestion.answer.value.length === 0) {
                            canGoToNext = false;
                        } else if (typeof firstQuestion.answer.value === "string" && firstQuestion.answer.value.length === 0) {
                            canGoToNext = false;
                        } else if (firstQuestion.answer.value.value && firstQuestion.answer.value.value.length === 0) {
                            canGoToNext = false;
                        }

                        if (!canGoToNext) {
                            break;
                        }
                    }
                }

                return canGoToNext ? newIndex : this.getNextQuestionIndex(index + 1);
            },

            getNextQuestion(index) {
                const newIndex = index + 1;
                const nextQuestion = this.questions[newIndex];
                if (!nextQuestion) {
                    return null;
                }

                //need to check if there are certain conditions that have to be met before showing this question
                if (nextQuestion.conditions && nextQuestion.conditions.length) {
                    let shouldDisable = false;
                    for (let i = 0; i < nextQuestion.conditions.length; i++) {
                        const q = nextQuestion.conditions;
                        const nextQuestConditions = q[i];

                        //we are evaluating only the first condition.related_question_order_number
                        //For now is OK since we are depending only on ONE related Question
                        const questions = this.getQuestionsOfOrder(nextQuestConditions.related_question_order_number);
                        const firstQuestion = questions[0];
                        if (!firstQuestion.answer || !firstQuestion.answer.value) {
                            shouldDisable = true;
                            break;
                        }

                        //If conditions needs to be compared against to "gte" or "lte"
                        if (nextQuestConditions.hasOwnProperty('operator')) {
                            const answerValue = Array.isArray(firstQuestion.answer.value.value) ? firstQuestion.answer.value.value[0] : firstQuestion.answer.value.value;

                            if (nextQuestConditions.operator === 'greater_or_equal_than') {
                                //Again we use only the first Question of the related Questions, which is OK for now.
                                shouldDisable = !(answerValue >= nextQuestConditions.related_question_expected_answer);
                                break;
                            }

                            if (nextQuestConditions.operator === 'less_or_equal_than') {
                                shouldDisable = !(answerValue <= nextQuestConditions.related_question_expected_answer);
                                break;
                            }
                        }
                        //default comparison
                        const expectedAnswersEqualsValue = q.map(c => c.related_question_expected_answer === firstQuestion.answer.value.value);

                        if (!expectedAnswersEqualsValue.includes(true)) {
                            shouldDisable = true;
                            break;
                        }
                        //if no expected answer, we look for any answer, if any
                        else if (typeof nextQuestConditions.related_question_expected_answer === "undefined") {
                            if (Array.isArray(firstQuestion.answer.value) && firstQuestion.answer.value.length === 0) {
                                shouldDisable = true;
                            } else if (typeof firstQuestion.answer.value === "string" && firstQuestion.answer.value.length === 0) {
                                shouldDisable = true;
                            } else if (firstQuestion.answer.value.value && firstQuestion.answer.value.value.length === 0) {
                                shouldDisable = true;
                            }

                            if (!shouldDisable) {
                                break;
                            }
                        }
                    }
                    nextQuestion.disabled = shouldDisable;
                }

                return !nextQuestion.disabled ? {
                    index: newIndex,
                    question: nextQuestion
                } : this.getNextQuestion(index + 1);
            },

            scrollToQuestion(questionId) {
                return new Promise((resolve) => {
                    const topButtonsOffset = $('.top-buttons').height() || 0;
                    const surveyContainer = $('.survey-container');
                    const currentQuestionOffset = $(`#${questionId}`).offset().top;

                    let scrollTo = 0;
                    if (currentQuestionOffset < 0) {
                        scrollTo = surveyContainer.scrollTop() + currentQuestionOffset - topButtonsOffset;
                    } else {
                        scrollTo = currentQuestionOffset - topButtonsOffset;
                    }

                    surveyContainer.scrollTo(
                        scrollTo,
                        500,
                        {
                            onAfter: () => {
                                setTimeout(() => this.setQuestionScrollVisibility(), 200);
                                resolve();
                            }
                        });
                });
            },

            goToNextQuestion(incrementProgress) {

                const next = this.getNextQuestion(this.currentQuestionIndex);

                //survey complete
                if (!next) {
                    this.stage = "complete";
                    this.latestQuestionAnsweredIndex = this.currentQuestionIndex;
                    this.currentQuestionIndex = this.currentQuestionIndex + 1;
                    if (incrementProgress) {
                        this.progress = this.progress + 1;
                    }
                    return Promise.resolve();
                }

                const nextQuestion = next.question;
                const nextIndex = next.index;

                return this.scrollToQuestion(nextQuestion.id)
                    .then(() => {
                        this.latestQuestionAnsweredIndex = this.currentQuestionIndex;
                        this.currentQuestionIndex = nextIndex;
                        const answered = this.questions[this.latestQuestionAnsweredIndex];

                        if (incrementProgress && answered.pivot.order !== nextQuestion.pivot.order) {
                            this.progress = this.progress + 1;
                        }

                        return Promise.resolve();
                    });
            },

            getQuestionsOfOrder(order) {
                return this.questions.filter(q => q.pivot.order === order);
            },

            calculateSurveyProgress() {
                const answers = this.surveyData.answers;
                if (!answers || !answers.length) {
                    this.progress = 0;
                    return;
                }

                let progress = 0;
                let lastOrder = -1;
                this.questions.forEach((q, index) => {

                    const a = answers.find(a => a.question_id === q.id);
                    if (a) {
                        q.answer = a;
                    }

                    if (lastOrder === q.pivot.order) {
                        return;
                    }

                    let increment = false;

                    if (a && a.value) {
                        //check if answer is actually answered and not just a suggested answer
                        increment = true;
                    } else {
                        // now figure out if an answer is expected from this question
                        // i.e. q32 might not be shown to the user because of its conditions
                        //      still, progress should be incremented
                        //      so:
                        //      - check if conditions of question have been answered
                        //        -> to make sure question is not shown because of answer value (not because answer does not exist)
                        //      - check if this question should be shown (this.getNextQuestion(index - 1))
                        //        -> to know whether an answer is expected for this question
                        //      - check if we have answers ahead of this question
                        //        -> if the two previous checks fail, it might be because user has not reached to that part of the survey yet
                        //           so, we check if there are answers ahead
                        const conditionsAnswered = this.isQuestionAnsweredFromQuestionConditions(index);
                        const isThisQuestionShownToUser = this.getNextQuestionIndex(index - 1) === index;
                        const hasAnswerAhead = this.hasAnswerAheadOfQuestion(index);
                        if (conditionsAnswered && !isThisQuestionShownToUser && hasAnswerAhead) {
                            increment = true;
                        }
                    }

                    if (increment) {
                        progress += 1;
                    }
                    lastOrder = q.pivot.order;
                });
                this.progress = progress;
            },

            toggleReadOnlyMode() {

                if (this.actionsDisabled) {
                    return;
                }

                this.actionsDisabled = true;

                if (this.readOnlyMode) {
                    const currentQuestion = this.questions[this.currentQuestionIndex];
                    this.scrollToQuestion(currentQuestion.id)
                        .then(() => {
                            this.readOnlyMode = !this.readOnlyMode;
                            this.actionsDisabled = false;
                        });
                } else {
                    this.readOnlyMode = !this.readOnlyMode;
                    this.actionsDisabled = false;
                }
            },

            toggleCallAssistance() {
                this.callAssistance = !this.callAssistance;
                if (this.callAssistance) {
                    const btnOffset = $('.call-btn-round').offset();
                    let modalOffset = $('.call-assistance-modal').height() + 10;
                    if (isNaN(modalOffset)) {
                        modalOffset = 260;
                    }
                    $('.call-assistance').offset({top: btnOffset.top - modalOffset, left: btnOffset.left});
                }
            },

            scrollQuestionToBottom() {
                $('.question-scroll-container').fadeOut();
                const elem = $('.question.active');
                elem.scrollTo(elem[0].scrollHeight, 500, {
                    onAfter: () => {

                    }
                });
            },

            setQuestionScrollVisibility() {

                const qScroll = $('.question-scroll-container');
                qScroll.css('position', 'fixed');

                if (this.readOnlyMode) {
                    qScroll.hide();
                    return;
                }

                const elem = $('.question.active')[0];

                if (elem.scrollHeight > (elem.offsetHeight + elem.scrollTop)) {

                    const bodyEl = $('body');
                    const bodyWidth = bodyEl.width();
                    const bodyHeight = bodyEl.height();

                    //got it from app.scss @media query...
                    const mobileMediaQuery = 490;

                    const staticLeftOffset = bodyWidth > mobileMediaQuery ? 110 : 90;
                    const staticTopOffset = bodyWidth > mobileMediaQuery ? 220 : 200;

                    const navbar = $('.bottom-navbar');
                    const leftOffset = navbar.offset().left + bodyWidth - staticLeftOffset;
                    qScroll.css('left', `${leftOffset}px`);

                    const topOffset = bodyHeight - staticTopOffset;
                    qScroll.css('top', `${topOffset}px`);

                    qScroll.fadeIn();
                } else {
                    qScroll.hide();
                }
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
            },

            goBack() {
                window.location.pathname = this.getPatientsListUrl();
            }
        },
        mounted() {
        },
        created() {

            const questionsData = this.surveyData.survey_instances[0].questions.map(function (q) {
                const result = Object.assign(q, {answer_types: [q.answer_type]});
                result.disabled = false; // we will be disabling based on answers
                return result;
            });
            //const questions = questionsData.filter(question => !question.optional);
            const subQuestions = questionsData.filter(question => question.optional);
            this.questions.push(...questionsData);
            this.subQuestions.push(...subQuestions);

            if (typeof this.surveyData.survey_instances[0].pivot.last_question_answered_id !== "undefined") {
                const lastQuestionAnsweredId = this.surveyData.survey_instances[0].pivot.last_question_answered_id;
                const index = this.questions.findIndex(q => q.id === lastQuestionAnsweredId);
                this.latestQuestionAnsweredIndex = index;
                const next = this.getNextQuestion(index);
                if (next) {
                    this.currentQuestionIndex = next.index;
                }
            }

            this.calculateSurveyProgress();

            this.totalQuestionWithSubQuestions = this.questions.length;
            this.totalQuestions = _.uniqBy(this.questions, (elem) => {
                return elem.pivot.order;
            }).length;

            const allQuestionsAnswered = this.surveyData.answers &&
                this.surveyData.answers.filter(a => !(a.value === null || typeof a.value === 'undefined')).length === this.questions.length;

            if (allQuestionsAnswered) {
                this.stage = "complete";
            }

            if (this.adminMode) {
                this.stage = "survey";
                this.readOnlyMode = true;
            }
        },

    }
</script>
