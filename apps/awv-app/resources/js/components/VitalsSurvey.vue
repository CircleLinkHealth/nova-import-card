<template>

    <!--
    DEPRECATED - DO NOT USE
    (only left here for future reference)
    -->


    <div class="container main-container vitals">

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
        <div class="survey-container" :class="{ max: stage === 'complete', 'read-only': readOnlyMode, 'with-top-buttons': stage !== 'welcome' }">
            <template v-if="stage === 'welcome'">
                <div class="card-body">
                    <div class="welcome-icon-container">
                        <img src="../../images/notepad-2.png"
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
                     :class="!readOnlyMode && currentQuestionIndex !== index ? 'watermark' : 'active'"
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
                                :get-all-questions-func="getAllQuestions"
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
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
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
                                :style-horizontal="true"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
                                :read-only="readOnlyMode"
                                v-if="question.type.type === 'radio'">
                            </question-type-radio>

                            <question-type-date
                                :is-active="currentQuestionIndex === index"
                                :is-subquestion="isSubQuestion(question)"
                                :get-all-questions-func="getAllQuestions"
                                :on-done-func="postAnswerAndGoToNext"
                                :is-last-question="isLastQuestion(question)"
                                :waiting="waiting"
                                :read-only="readOnlyMode"
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
                <div v-if="!readOnlyMode" style="height: 600px"></div>
            </template>
            <template v-else>
                <div class="card-body">
                    <div class="welcome-icon-container">
                        <img src="../../images/doctors.png"
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
                            If you are using the patient's phone, please <strong>logout</strong> and hand it back now.
                        </div>

                    </div>

                    <div class="btn-start-container">
                        <mdb-btn color="primary" class="btn-start" @click="logout">
                            Logout
                        </mdb-btn>

                        <form id="logout-form" action="/logout" method="POST" style="display: none;">
                        </form>
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

    import {mdbBtn, mdbProgress, mdbRow, mdbCol, mdbIcon} from 'mdbvue';

    import questionTypeText from "./questionTypeText";
    import questionTypeCheckbox from "./questionTypeCheckbox";
    import questionTypeRange from "./questionTypeRange";
    import questionTypeNumber from "./questionTypeNumber";
    import questionTypeRadio from "./questionTypeRadio";
    import questionTypeDate from "./questionTypeDate";
    import questionTypeMultiSelect from "./questionTypeMultiSelect";

    export default {
        props: ['data', 'adminMode'],

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
            'question-type-muti-select': questionTypeMultiSelect
        },

        data() {
            return {
                stage: "welcome",
                waiting: false,
                actionsDisabled: false,
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
                readOnlyMode: false,
            }
        },
        computed: {
            canScrollUp() {
                return (this.stage === "survey" || this.stage === "complete")
                    && this.currentQuestionIndex > 0;
            },
            canScrollDown() {

                const canProceed = this.stage === "survey"
                    && this.currentQuestionIndex < this.questions.length;

                let nextHasAnswer = false;
                if (canProceed) {
                    const nextQuestionIndex = this.getNextQuestionIndex(this.currentQuestionIndex);
                    if (nextQuestionIndex !== this.currentQuestionIndex) {
                        const nextQuestion = this.questions[nextQuestionIndex];
                        nextHasAnswer = typeof nextQuestion !== "undefined" &&
                            typeof nextQuestion.answer !== "undefined" &&
                            !(nextQuestion.answer.value === null || typeof nextQuestion.answer.value === "undefined");
                    }

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

            startSurvey() {
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

            scrollToQuestion(questionId) {
                return new Promise((resolve) => {
                    const surveyContainer = $('.survey-container');
                    const currentQuestionOffset = $(`#${questionId}`).offset().top;

                    let scrollTo = 0;
                    if (currentQuestionOffset < 0) {
                        scrollTo = surveyContainer.scrollTop() + currentQuestionOffset;
                    } else {
                        scrollTo = currentQuestionOffset
                    }

                    surveyContainer.scrollTo(
                        scrollTo,
                        500,
                        {
                            onAfter: () => {
                                resolve();
                            }
                        });
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
                        const q = prevQuestion.conditions[0];
                        const questions = this.getQuestionsOfOrder(q.related_question_order_number);
                        const firstQuestion = questions[0];
                        if (!firstQuestion.answer || firstQuestion.answer.value.value !== q.related_question_expected_answer) {
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
                if (nextQuestion.conditions && nextQuestion.conditions.length) {
                    for (let i = 0; i < nextQuestion.conditions.length; i++) {
                        const q = nextQuestion.conditions[0];
                        const questions = this.getQuestionsOfOrder(q.related_question_order_number);
                        const firstQuestion = questions[0];

                        if (!firstQuestion.answer || firstQuestion.answer.value.value !== q.related_question_expected_answer) {
                            canGoToNext = false;
                            break;
                        }
                        //if no expected answer, we look for any answer, if any
                        else if (typeof q.related_question_expected_answer === "undefined") {
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
                }
                return canGoToNext ? newIndex : this.getNextQuestionIndex(index + 1);
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

            postAnswerAndGoToNext(questionId, questionTypeAnswerId, answer, isLastQuestion) {

                if (this.actionsDisabled) {
                    return;
                }

                this.actionsDisabled = true;
                this.error = null;
                this.waiting = true;

                return axios
                    .post(`/survey/vitals/${this.patientId}/save-answer`, {
                        patient_id: this.patientId,
                        practice_id: this.practiceId,
                        survey_instance_id: this.surveyInstanceId,
                        question_id: questionId,
                        question_type_answer_id: questionTypeAnswerId,
                        value: answer,
                        survey_complete: isLastQuestion
                    })
                    .then((response) => {
                        this.waiting = false;
                        //save the answer in state
                        const q = this.questions.find(x => x.id === questionId);

                        //increment progress only if question was not answered before
                        const incrementProgress = typeof q.answer === "undefined" || (typeof q.answer.value === "undefined" || q.answer.value === null);
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
                return questions.every(q => q.answer !== undefined && !(q.answer.value === null || q.answer.value === undefined));
            },

            toggleReadOnlyMode() {

                if (this.actionsDisabled) {
                    return;
                }

                this.actionsDisabled = true;

                if (this.readOnlyMode) {
                    const currentQuestion = this.questions[this.currentQuestionIndex];
                    $('.survey-container').animate({
                        scrollTop: $(`#${currentQuestion.id}`).offset().top
                    }, 519, 'swing', () => {
                        this.readOnlyMode = !this.readOnlyMode;
                        this.actionsDisabled = false;
                    });
                } else {
                    this.readOnlyMode = !this.readOnlyMode;
                    this.actionsDisabled = false;
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
            //clone props into data
            this.patientId = this.data.id;
            this.practiceId = this.data.program_id;
            this.patientName = this.data.display_name;
            this.surveyInstanceId = this.data.survey_instances[0].id;
            this.questions = this.data.survey_instances[0].questions.slice(0);

            if (this.data.answers && this.data.answers.length) {
                let lastOrder = -1;
                this.questions.forEach(q => {
                    const a = this.data.answers.find(a => a.question_id === q.id);
                    if (a) {
                        q.answer = a;
                        if (a.value && lastOrder !== q.pivot.order) {
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
                if (this.latestQuestionAnsweredIndex >= (this.questions.length - 1)) {
                    this.currentQuestionIndex = 0;
                } else {
                    this.currentQuestionIndex = this.latestQuestionAnsweredIndex + 1;
                }
            }

            this.totalQuestionWithSubQuestions = this.questions.length;
            this.totalQuestions = _.uniqBy(this.questions, (elem) => {
                return elem.pivot.order;
            }).length;

            const allQuestionsAnswered = this.data.answers &&
                this.data.answers.filter(a => !(a.value === null || typeof a.value === 'undefined')).length === this.questions.length;

            if (allQuestionsAnswered) {
                this.stage = "complete";
            }

            if (this.adminMode) {
                this.stage = "survey";
                this.readOnlyMode = true;
            }
        }
    }
</script>
<style lang="scss" scoped>

</style>
