<template>
    <div>
        <!--question without sub_parts-->
        <div v-if="!questionHasSubParts">
            <input
                type="number"
                class="number-field"
                name="numberTypeAnswer[]"
                v-model="inputNumber"
                :disabled="!isActive"
                :placeholder="this.questionPlaceHolder">
        </div>

        <!--question with sub_parts-->
        <div v-if="questionHasSubParts">
            <div v-for="(subPart, index) in questionSubParts" :key="index" style="display: inline">
                <input type="number"
                       class="number-field"
                       :class="subPartsStyle"
                       name="numberTypeAnswer[]"
                       v-model="inputNumber[index]"
                       :disabled="!isActive"
                       :placeholder="subPart.placeholder">

                <span
                    v-if="questionSubPartsSeparator === 'dash' && index !== questionSubParts.length - 1">
                    &nbsp;/&nbsp;
                </span>

                <span
                    v-if="questionSubPartsSeparator === '' && index !== questionSubParts.length - 1">
                    &nbsp;
                </span>
            </div>
        </div>

        <br>

        <mdbBtn v-show="isActive"
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
    import {mdbBtn} from "mdbvue";

    export default {
        name: "questionTypeNumber",
        components: {mdbBtn},
        props: ['question', 'userId', 'surveyInstanceId', 'isActive', 'isSubQuestion', 'onDoneFunc', 'isLastQuestion'],

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

            subPartsStyle() {
                return 'parts-' + this.questionSubParts.length;
            },

            hasTypedTwoNumbers() {
                return this.isActive && this.inputNumber.length > 1;
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
                    return this.questionOptions[0].hasOwnProperty('sub_parts') || this.questionOptions[0].hasOwnProperty('sub-parts');
                }
                return false;
            },

            questionSubParts() {
                if (this.questionHasSubParts) {
                    return this.questionOptions[0].sub_parts || this.questionOptions[0]["sub-parts"];
                }
                return [];
            },

            questionSubPartsSeparator() {
                return this.questionOptions[0].separate_sub_parts_with || '';
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

                this.onDoneFunc(this.question.id, answer)
                    .catch(err => {
                        //if there is error, app will not move to next question.
                        //handle it here
                    });
            }
        },
        created() {
            const questionOptions = this.question.type.question_type_answers.map(q => q.options);
            this.questionOptions.push(...questionOptions);

            if (this.questionSubParts.length > 1) {
                const keys = (this.questionOptions[0].sub_parts || this.questionOptions[0]["sub-parts"]).map(q => q.key);
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

    .number-field {
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
        width: 300px;
        height: 30px;
    }

    .number-field.parts-2 {
        width: 120px;
    }
</style>
