<template>
    <div class="container">

        <!--Survey welcome note-->
        <div class="card">
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
                 v-for="(question, index) in orderedQuestions">
                <div v-show="index >= questionIndex" class="question">
                    <div v-if="">{{question.id}}{{'.'}} {{question.body}}
                        <!--  <sub-questions v-if="" :question="question"></sub-questions>-->
                        <br>
                        <!--Questions Answer Type-->
                        <div class="question-answer-type">
                            <question-type-text v-if="question.type.answer_type === 'text'"></question-type-text>
                            <question-type-checkbox
                                    v-if="question.type.answer_type === 'checkbox'"></question-type-checkbox>
                            <question-type-range v-if="question.type.answer_type === 'range'"></question-type-range>
                            <question-type-number v-if="question.type.answer_type === 'number'"></question-type-number>
                            <question-type-radio :question="question"
                                                 v-if="question.type.answer_type === 'radio'"></question-type-radio>
                            <question-type-date v-if="question.type.answer_type === 'date'"></question-type-date>
                        </div>
                    </div>
                </div>
            </div>

            <!--bottom-navbar-->
            <br>
            <call-assistance v-if="callAssistance" @closeCallAssistanceModal="hideCallHelp"></call-assistance>
            <div class="bottom-navbar">
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
                    <button type="button"
                            id="next-button"
                            class="btn btn-sm next"
                            @click="nextQuestions">
                        <i class="fas fa-angle-down"></i>
                    </button>
                    <button v-if="questionIndex > 0" type="button"
                            id="previous-button"
                            class="btn btn-sm next"
                            @click="previousQuestions">
                        <i class="fas fa-angle-up"></i>
                    </button>
                </div>
            </div>
        </div>
        <!--<div class="inputArea" v-for="input in inputs" :key="input.id">
            <input type="text" name="textTypeAnswer">
        </div>
        <button @click="addInput">Add input</button>-->
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
        },

        data() {
            return {
                showPhoneButton: true,
                questionsStage: false,
                welcomeStage: true,
                callAssistance: false,
                questionsData: [],
                questionIndex: 0,
                areSubQuestions:[]
                /*   counter: 0,
                   inputs: [{
                       id: 'fruit0',
                           label: 'Enter Fruit Name',
                       value: '',
                   }],*/
            }
        },
        computed: {
            questions(){
                return this.questionsData.flat(1);
            },
            sub(){
                return this.areSubQuestions.flat(1);
            },
            orderedQuestions() {
                /*todo:this has to change to question->pivot->order */
                return _.orderBy(this.questions, 'id')
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

            nextQuestions() {
                this.questionIndex++;
            },

            previousQuestions() {
                this.questionIndex--;
            },

            /*addInput() {
                this.inputs.push({
                    id: `fruit${++this.counter}`,
                    label: 'Enter Fruit Name',
                    value: '',
                });
            }*/

        },
        created() {
            const questionsData = this.surveydata.survey_instances[0].questions.map(function (questions) {
                return questions
            });

            const x = questionsData.map(function (question) {
                return question.conditions.isSubQuestion === true
            });

            this.areSubQuestions.push(x);
            this.questionsData.push(questionsData);
        },

    }
</script>

<style scoped>
    .questions-box {
        padding-top: 5%;
        padding-left: 15%;
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

    .card {
        margin-top: 50px;
        background-color: #f2f6f9;
        border-top: 1px solid #808080;
        border-left: 1px solid #808080;
        border-right: 1px solid #808080;
        width: 100%;
        min-height: 700px;
    }

    #previous-button, #next-button {
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
</style>
