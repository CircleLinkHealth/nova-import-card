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
        props: ['question', 'userId', 'surveyInstanceId'],
        components: {},

        data() {
            return {
                possibleAnswers: [],
                questionOrder: this.question.pivot.order,
                questionOptions: [],
                differentInputTypesData: [],
                showDifferentInput: false,
                inputHasText: [],
            }
        },

        computed: {
            questionHasConditions() {
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

            /*answerIsYesOrNo() {


            },*/
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

                axios.post('/save-answer', {
                    user_id: this.userId,
                    survey_instance_id: this.surveyInstanceId[0],
                    question_id: this.question.id,
                    question_type_answer_id: questionTypeAnswerId[0],
                    value: answerData,
                })
                    .then(function (response) {
                        console.log(response);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });

                EventBus.$emit('showSubQuestions', answerVal, this.questionOrder, this.question.id)
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