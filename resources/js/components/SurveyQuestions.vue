<template>
    <div class="container main-container">
        <!--Survey welcome note-->
        <div class="survey-container" :class="stage === 'complete' ? 'max' : ''">
            <template v-if="stage === 'welcome'">
                <div class="practice-title">
                    <label id="title">
                        <strong>{{practiceName}}</strong>
                        <br/>
                        Dr. {{doctorsLastName}}’s Office
                    </label>
                </div>
                <div class="card-body">
                    <img src="../../assets/images/notepad.png"
                         class="welcome-icon" alt="welcome icon">
                    <div class="survey-main-title">
                        <label id="sub-title">Annual Wellness Visit (AWV) Questionnaire</label>
                    </div>
                    <div class="survey-sub-welcome-text">Welcome to your
                        Annual Wellness Visit (AWV) Questionnaire! Understanding your health is of upmost importance to
                        us,
                        so thank you for taking time to fill this out.
                        If there’s any question you have trouble answering, feel free to click the call button on the
                        bottom
                        left and a representative will help when you call the number. If you skip any questions, our
                        reps
                        will also reach out shortly. Thanks!
                    </div>


                    <div class="btn-start-container">
                        <!-- @todo: this is not working exactly as expected so im keepin one element true and i ll get back-->
                        <mdb-btn v-show="true"
                                 color="primary" class="btn-start" @click="showQuestions">
                            <span v-if="progress === 0">Start</span>
                            <span v-else>Continue</span>
                        </mdb-btn>

                        <!-- <mdb-btn v-show="lastQuestionAnswered === null"
                                  color="primary" class="btn-start" @click="showQuestions">
                             <span>Start</span>
                         </mdb-btn>

                         <mdb-btn v-if="lastQuestionAnswered !== null"
                                  color="primary" class="btn-start" @click="scrollToLastQuestion">
                             <span>Continue</span>
                         </mdb-btn>-->
                    </div>
                    <div class="by-circlelink">
                        ⚡️ by CircleLink Health
                    </div>
                </div>
            </template>


            <!--Questions-->
            <template v-if="stage === 'survey'">
                <div class="questions-box question"
                     :id="question.id"
                     :class="currentQuestionIndex !== index ? (question.conditions && question.conditions.length > 0 ? 'non-visible' : 'watermark') : 'active'"
                     v-show="index >= currentQuestionIndex"
                     v-for="(question, index) in questions">
                    <div class="questions-body">

                        <div class="questions-title">
                            {{getQuestionTitle(question)}}
                        </div>

                        <br>

                        <!--Questions Answer Type-->
                        <div class="question-answer-type">
                            <question-type-text
                                :question="question"
                                :is-active="currentQuestionIndex === index"
                                :on-done-func="postAnswerAndGoToNext"
                                :waiting="waiting"
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
                                v-if="question.type.type === 'multi_select'">
                            </question-type-muti-select>

                            <question-type-range
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
                                v-if="question.type.type === 'radio'">
                            </question-type-radio>

                            <question-type-date
                                v-if="question.type.type === 'date'">
                            </question-type-date>
                        </div>
                    </div>
                    <div class="error" v-if="error">
                        <span v-html="error"></span>
                    </div>
                </div>
                <!-- add an empty div, so we can animate scroll up even if we are on last question -->
                <div style="height: 600px"></div>
            </template>
        </div>
        <div class="call-assistance">
            <call-assistance v-if="callAssistance" @closeCallAssistanceModal="hideCallHelp"></call-assistance>
        </div>


        <!--bottom-navbar-->
        <!--@todo: this is the call assistance modal. needs some styling and setup twilio-->
        <!--&lt;!&ndash;phone assistance&ndash;&gt;
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
-->
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
    import callAssistance from "./callAssistance";
    import questionTypeMultiSelect from "./questionTypeMultiSelect";


    export default {
        props: ['surveyData'],

        components: {
            'mdb-btn': mdbBtn,
            'mdb-progress': mdbProgress,
            'question-type-text': questionTypeText,
            'question-type-checkbox': questionTypeCheckbox,
            'question-type-range': questionTypeRange,
            'question-type-number': questionTypeNumber,
            'question-type-radio': questionTypeRadio,
            'question-type-date': questionTypeDate,
            'call-assistance': callAssistance,

            'question-type-muti-select': questionTypeMultiSelect
        },

        data() {
            return {
                stage: "welcome",
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
                userId: this.surveyData.id,
                surveyInstanceId: null,
                questionIndexAnswers: [],
                conditionsLength: 0,
                latestQuestionAnsweredIndex: -1,
                currentQuestionIndex: 0,
                error: null,
                progress: 0,
                waiting: false,
                practiceId: null,
                practiceName: null,
                doctorsLastName: null,
                totalQuestions: 0,
                totalQuestionWithSubQuestions: 0
            }
        },

        computed: {
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
                return this.stage === "survey"
                    && this.currentQuestionIndex < this.totalQuestions
                    && this.latestQuestionAnsweredIndex >= this.currentQuestionIndex;
            },
            progressPercentage() {
                return 100 * (this.progress) / this.totalQuestions;
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
                this.stage = "survey";
            },

            scrollUp() {
                if (this.currentQuestionIndex === 0) {
                    return;
                }

                this.error = null;
                this.currentQuestionIndex = this.getPreviousQuestionIndex(this.currentQuestionIndex);
            },

            scrollDown() {
                if (this.latestQuestionAnsweredIndex < this.currentQuestionIndex) {
                    return;
                }

                this.error = null;
                this.currentQuestionIndex = this.currentQuestionIndex + 1;
            },

            isSubQuestion(question) {
                return question.pivot.sub_order !== null;
            },

            isLastQuestion(question) {
                return this.questions[this.questions.length - 1].id === question.id;
            },
            shouldShowQuestionGroupTitle(question) {
                return question.pivot.sub_order != null && (question.pivot.sub_order === "a" || question.pivot.sub_order === "1");
            },

            getQuestionTitle(question) {
                if (this.isSubQuestion(question)) {
                    return `${question.pivot.order}${question.pivot.sub_order}. ${question.body}`;
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


            postAnswerAndGoToNext(questionId, questionTypeAnswerId, answer) {

                this.error = null;
                this.waiting = true;

                axios.post(`/survey/hra/${this.practiceId}/${this.userId}/save-answer`, {
                    patient_id: this.userId,
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
                                this.currentQuestionIndex = this.currentQuestionIndex - 1;
                                this.$nextTick().then(() => {
                                    this.currentQuestionIndex = this.currentQuestionIndex + 1;
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
                        }
                        else if (error.response && error.response.data) {
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
                        const q = prevQuestion.conditions[0];
                        const questions = this.getQuestionsOfOrder(q.related_question_order_number);
                        if (questions[0].answer.value.value !== q.related_question_expected_answer) {
                            canGoToPrev = false;
                            break;
                        }
                    }
                }
                return canGoToPrev ? newIndex : this.getPreviousQuestionIndex(index - 1);
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
                        const q = nextQuestion.conditions[0];
                        const questions = this.getQuestionsOfOrder(q.related_question_order_number);
                        if (questions[0].answer.value.value !== q.related_question_expected_answer) {
                            shouldDisable = true;
                            break;
                        }
                    }
                    nextQuestion.disabled = shouldDisable;
                }

                return !nextQuestion.disabled ? {
                    index: newIndex,
                    question: nextQuestion
                } : this.getNextQuestion(index + 1);
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
                    return;
                }

                const nextQuestion = next.question;
                const nextIndex = next.index;

                return new Promise(resolve => {
                    $('.survey-container').animate({
                        scrollTop: $(`#${nextQuestion.id}`).offset().top
                    }, 519, 'swing', () => {
                        this.latestQuestionAnsweredIndex = this.currentQuestionIndex;
                        this.currentQuestionIndex = nextIndex;
                        const answered = this.questions[this.latestQuestionAnsweredIndex];

                        //increment progress only if current question is not a sub question
                        if (answered.pivot.sub_order === null) {
                            if (incrementProgress) {
                                this.progress = this.progress + 1;
                            }
                        } else {
                            //if this is the last sub question of a group, increment progress

                            //get all sub questions and sort them (i.e ["a", "b", "c"]
                            const allSubs = this.questions.filter(q => q.pivot.order === answered.pivot.order).map(q => q.pivot.sub_order).sort();
                            if (allSubs[allSubs.length - 1] === answered.pivot.sub_order) {
                                if (incrementProgress) {
                                    this.progress = this.progress + 1;
                                }
                            }
                        }
                        resolve();
                    });
                });
            },

            hasAnsweredAllOfOrder(order) {
                const questions = this.questions.filter(q => q.pivot.order === order);
                return questions.every(q => q.answer !== undefined);
            },

            getQuestionsOfOrder(order) {
                return this.questions.filter(q => q.pivot.order === order);
            }

        },
        mounted() {
        },
        created() {

            this.practiceId = this.surveyData.primary_practice.id;
            this.practiceName = this.surveyData.primary_practice.display_name;
            this.doctorsLastName = this.surveyData.billing_provider[0].user.last_name;

            this.surveyInstanceId = this.surveyData.survey_instances[0].id

            const questionsData = this.surveyData.survey_instances[0].questions.map(function (q) {
                const result = Object.assign(q, {answer_types: [q.answer_type]});
                result.disabled = false; // we will be disabling based on answers
                return result;
            });
            const questions = questionsData.filter(question => !question.optional);
            const subQuestions = questionsData.filter(question => question.optional);
            this.questions.push(...questionsData);
            this.subQuestions.push(...subQuestions);

            if (this.surveyData.answers && this.surveyData.answers.length) {
                this.surveyData.answers.forEach(a => {
                    const q = this.questions.find(q => q.id === a.question_id);
                    if (q) {
                        q.answer = a;
                        if (q.pivot.sub_order === null || this.hasAnsweredAllOfOrder(q.pivot.order)) {
                            this.progress = this.progress + 1;
                        }
                    }
                });
            }

            if (typeof this.surveyData.survey_instances[0].pivot.last_question_answered_id !== "undefined") {
                const lastQuestionAnsweredId = this.surveyData.survey_instances[0].pivot.last_question_answered_id;
                const index = this.questions.findIndex(q => q.id === lastQuestionAnsweredId);
                this.latestQuestionAnsweredIndex = index;
                const next = this.getNextQuestion(index);
                if (next) {
                    this.currentQuestionIndex = next.index;
                }
            }

            this.totalQuestionWithSubQuestions = this.questions.length;
            this.totalQuestions = _.uniqBy(this.questions, (elem) => {
                return elem.pivot.order;
            }).length;

            if (this.surveyData.answers && this.surveyData.answers.length === this.questions.length) {
                this.stage = "complete";
            }
        },

    }
</script>

<style lang="scss" scoped>

</style>
