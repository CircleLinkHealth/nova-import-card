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

        <mdbBtn v-show="showNextButton"
                color="primary"
                class="next-btn"
                name="number"
                id="number"
                :disabled="!hasTypedTwoNumbers"
                @click="handleAnswer()">
            Next
        </mdbBtn>

    </div>
</template>

<script>
    import {EventBus} from "../event-bus";
    import {mdbBtn} from "mdbvue";
    /* import {saveAnswer} from "../save-answer";*/

    export default {
        name: "questionTypeNumber",
        components: {mdbBtn},
        props: ['question', 'userId', 'surveyInstanceId', 'showNextButton', 'onDone'],

        mounted() {
            console.log('Component mounted.')
        },

        data() {
            return {
                inputNumber: [],
                questionOptions: [],
                keys: [],
            }
        },
        computed: {
            hasTypedTwoNumbers() {
                return this.showNextButton && this.inputNumber.length > 1;
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

                if (!this.hasTypedTwoNumbers) {
                    return;
                }

                const answer = {
                    value: this.inputNumber,
                };

                this.onDone(answer)
                    .catch(err => {
                        //if there is error, app will not move to next question.
                        //handle it here
                    });
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

    .btn-primary {
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .btn-primary.disabled {
        opacity: 50%;
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .next-btn {
        width: 120px;
        height: 40px;
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
