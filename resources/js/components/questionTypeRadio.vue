<template>
    <div>
        <div class="radio">
            <div class="row">
                <div v-for="answer in possibleAnswers">
                    <label>{{answer.value}}
                        <input id="question"
                               :name="question.id"
                               :value="answer.value"
                               type="radio"
                               @change="handleAnswer(answer.value)">
                    </label>
                </div>

                <div v-if="showDifferentInput"
                     v-for="input in differentInputTypesData">
                    <input id="different-input"
                           class="text-field"
                           name="textTypeAnswer"
                           v-model="inputHasText"
                           :placeholder="input.placeholder"
                           :type="input.type">
                </div>
            </div>

            <!--next button-->
            <div v-if="inputHasText >'1'">
                <button class="next-btn"
                        name="text"
                        id="text"
                        type="submit"
                        @click="handleAnswer(inputHasText)">Next
                </button>
            </div>
        </div>
    </div>
</template>

<script>

    import {EventBus} from "../event-bus";

    export default {
        name: "questionTypeRadio",
        props: ['question', 'onDoneFunc'],
        components: {},

        data() {
            return {
                possibleAnswers: [],
                questionOrder: this.question.pivot.order,
                questionOptions: [],
                differentInputTypesData: [],
                showDifferentInput: false,
                inputHasText: [],
                isYesOrNoQuestion: false,
                isSubQuestion:false,
            }
        },

        computed: {
            questionHasConditions() {
                this.isSubQuestion = true;
                return this.question.conditions != null;
            },

            hasOptions() {
                return this.questionOptions.length !== 0;
            },

            hasDifferentInputType() {
                if (this.hasOptions) {
                    const options = this.questionOptions.map(a => a.options);
                    return options[0].hasOwnProperty('answer_type') ? this.showDifferentInput = true : '';
                }
                return false;
            },

            isYesOrNoQuestionTypeAnswer() {
                if (this.hasDifferentInputType) {
                    return this.questionOptions.map(function (q) {
                        return q.options.hasOwnProperty('yes_or_no_question') ? this.isYesOrNoQuestion = true : '';
                    });
                }
                return false;

            },
        },


        methods: {
            handleAnswer(answerVal) {

                const questionTypeAnswerId = this.possibleAnswers.filter(possibleAnswer => {
                    /*what i want to say is - if value === null*/
                    if (possibleAnswer.value !== answerVal) {
                        return possibleAnswer;
                    }
                    return possibleAnswer.value === answerVal;
                }).map(questionTypeAnswer => questionTypeAnswer.id);

                var answer = {
                    value: answerVal
                };

                var answerData = JSON.stringify(answer);
               // EventBus.$emit('showSubQuestions', answerVal, this.questionOrder, this.question.id, this.isSubQuestion);
                this.onDoneFunc(this.question.id, questionTypeAnswerId[0], answerData);
            },

        },

        created() {
            const questionOptions = this.question.type.question_type_answers.filter(question => question.options);
            this.questionOptions.push(...questionOptions);

            const possibleAnswers = this.question.type.question_type_answers.filter(possibleAnswer => possibleAnswer.value);
            this.possibleAnswers.push(...possibleAnswers);

            /*fetch input type & placeholder for the second input type*/
            if (this.hasDifferentInputType) {
                const inputTypeData = this.questionOptions.map(a => a.options);
                this.differentInputTypesData.push(...inputTypeData);
            }


        }
    }
</script>

<style scoped>
    .radio label {
        width: 420px;
        height: 50px;
        border-radius: 5px;
        border: solid 1px #4aa5d2;
        background-color: #ffffff;
        margin-left: .5rem;
        padding-left: 3%;
        padding-top: 3%;
    }

    .radio label > text {
        padding-left: 3px;
    }

    .radio input[type="radio"] {
        /*  display: none;*/

    }

    .radio input[type="radio"]:checked + label {
        background-color: #4aa5d2;
    }

</style>