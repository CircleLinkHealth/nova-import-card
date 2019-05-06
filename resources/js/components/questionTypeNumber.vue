<template>
    <div>
        <!--question without sub_parts-->
        <div v-if="!questionHasSubParts">
            <input
                    type="number"
                    class="number-field"
                    name="numberTypeAnswer[]"
                    v-model="inputNumber"
                    :placeholder="this.questionPlaceHolder">
        </div>
        <br>
        <!--question with sub_parts-->
        <div v-if="questionHasSubParts" class="row">
            <div v-for="(subPart, index) in questionSubParts" :key="index">
                <input type="number"
                       class="number-field"
                       name="numberTypeAnswer[]"
                       v-model="inputNumber[index]"
                       :placeholder="subPart.placeholder">
            </div>

        </div>

        <!--next button-->
        <div v-if="hasTypedTwoNumbers">
            <button class="next-btn"
                    name="number"
                    id="number"
                    type="submit"
                    @click="handleAnswer()">Next
            </button>

        </div>
    </div>
</template>

<script>
    /* import {saveAnswer} from "../save-answer";*/

    export default {
        name: "questionTypeNumber",
        props: ['question', 'onDoneFunc'],

        mounted() {
            console.log('Component mounted.')
        },

        data() {
            return {
                inputNumber: [],
                questionOptions: [],
                showNextButton: false,
                keys: [],
            }
        },
        computed: {
            hasTypedTwoNumbers() {
                return this.inputNumber.length > 1 ? this.showNextButton = true : this.showNextButton = false;
            },

            hasAnswerType() {
                return this.question.type.question_type_answers.length !== 0;
            },

            questionTypeAnswerId() {
                if (this.hasAnswerType) {
                    return this.question.type.question_type_answers[0].id;
                } else {
                    return 0;
                }
            },

            questionHasSubParts() {
                if (this.hasAnswerType) {
                    return this.questionOptions[0].hasOwnProperty('sub_parts');
                }
                return false;
            },

            questionSubParts() {
                if (this.questionHasSubParts) {
                    return this.questionOptions[0].sub_parts;
                }
                return '';
            },

            questionHasPlaceHolder() {
                if (this.hasAnswerType) {
                    return this.questionOptions[0].hasOwnProperty('placeholder');
                }
                return false;
            },

            questionPlaceHolder() {
                if (this.questionHasPlaceHolder) {
                    return this.questionOptions[0].placeholder;
                }
                return '';
            },

        },

        methods: {
            handleAnswer() {
                const inputVal = this.inputNumber;
                const keys = this.keys;
                if (keys.length !== 0) {
                    var answer = inputVal.reduce(function (result, field, index) {
                        result[keys[index]] = field;
                        return result;
                    }, {});

                } else {
                    var answer = {
                        value: inputVal
                    };
                }
                var answerData = JSON.stringify(answer);

                /* axios.post('/save-answer', {
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
                     });*/
                /*EventBus.$emit('handleNumberType');*/
                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answerData);
            }
        },
        created() {
            const questionOptions = this.question.type.question_type_answers.map(q => q.options);
            this.questionOptions.push(...questionOptions);

            if (this.questionSubParts !== '') {
                const keys = this.questionOptions[0].sub_parts.map(q => q.key);
                this.keys.push(...keys);
            }
        },
    }
</script>

<style scoped>
    .next-btn {
        width: 120px;
        height: 40px;
        border-radius: 5px;
        border: solid 1px #4aa5d2;
        background-color: #50b2e2;
    }

    .number-field {
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
        width: 300px;
        height: 30px;
    }
</style>