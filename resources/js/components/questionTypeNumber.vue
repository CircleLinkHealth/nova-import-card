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
        <br>
        <!--question with sub_parts-->
        <div v-if="questionHasSubParts" class="row">
            <div v-for="(subPart, index) in questionSubParts" :key="index">
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

        <!--next button-->
        <br>
        <mdbBtn v-show="isActive"
                color="primary"
                class="next-btn"
                name="number"
                id="number"
                :disabled="!hasTypedTwoNumbers"
                @click="handleAnswer()">
            {{isLastQuestion ? 'Complete' : 'Next'}}
            <font-awesome-icon v-show="waiting" icon="spinner" :spin="true"/>
        </mdbBtn>
    </div>
</template>

<script>

    import {mdbBtn} from "mdbvue";
    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faSpinner} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
    library.add(faSpinner);


    export default {
        name: "questionTypeNumber",
        props: ['question', 'userId', 'surveyInstanceId', 'isActive', 'isSubQuestion', 'onDoneFunc', 'isLastQuestion', 'waiting'],
        components: {mdbBtn, FontAwesomeIcon},

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
            subPartsStyle() {
                return 'parts-' + this.questionSubParts.length;
            },

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
                    return undefined;
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

                /*EventBus.$emit('handleNumberType');*/
                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer);
            }
        },
        created() {
            const questionOptions = this.question.type.question_type_answers.map(q => q.options);
            this.questionOptions.push(...questionOptions);

            if (this.question.answer) {
                this.inputNumber = this.question.answer.value.value;
            }

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
