<template>
    <div>
        <!--question without sub_parts-->
        <div v-if="!questionHasSubParts">
            <input
                type="number"
                class="number-field"
                name="numberTypeAnswer[]"
                v-model="inputNumbers[0]"
                :disabled="readOnly || !isActive"
                :placeholder="this.questionPlaceHolder">
        </div>
        <br>
        <!--question with sub_parts-->
        <div v-if="questionHasSubParts" class="row no-margin-side">
            <div v-for="(subPart, index) in questionSubParts" :key="index">
                <input type="number"
                       class="number-field"
                       :class="subPartsStyle"
                       name="numberTypeAnswer[]"
                       v-model="inputNumbers[index]"
                       :disabled="readOnly || !isActive"
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
        <mdbBtn v-show="!readOnly && isActive"
                color="primary"
                class="next-btn"
                name="number"
                id="number"
                :disabled="!hasAnyInput"
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
        props: ['question', 'isActive', 'isSubQuestion', 'onDoneFunc', 'isLastQuestion', 'waiting', 'readOnly'],
        components: {mdbBtn, FontAwesomeIcon},

        mounted() {
        },

        data() {
            return {
                inputNumbers: [],
                questionOptions: [],
                showNextButton: false,
                keys: [],
            }
        },
        computed: {
            subPartsStyle() {
                return 'parts-' + this.questionSubParts.length;
            },

            hasAnyInput() {
                return this.inputNumbers[0].length > 0 ? this.showNextButton = true : this.showNextButton = false;
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
                if (!this.hasAnyInput) {
                    return;
                }

                const inputVal = this.inputNumbers;
                const keys = this.keys;
                let answer;
                if (keys.length !== 0) {
                    answer = inputVal.reduce(function (result, field, index) {
                        result[keys[index]] = field;
                        return result;
                    }, {});

                } else {
                    answer = {
                        value: inputVal
                    };
                }

                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer, this.isLastQuestion);
            }
        },
        created() {
            const questionOptions = this.question.type.question_type_answers.map(q => q.options);
            this.questionOptions.push(...questionOptions);

            if (this.question.answer && this.question.answer.value) {
                if (typeof this.question.answer.value === "string") {
                    this.inputNumbers.push(this.question.answer.value)
                }
                else if (Array.isArray(this.question.answer.value)) {
                    this.inputNumbers = this.question.answer.value;
                }
                else {
                    //assume object
                    this.inputNumbers = Object.values(this.question.answer.value);
                }
            }

            if (this.questionSubParts.length > 1) {
                const keys = (this.questionOptions[0].sub_parts || this.questionOptions[0]["sub-parts"]).map(q => q.key);
                this.keys.push(...keys);
                if (this.inputNumbers.length === 0) {
                    //just add empty inputs
                    this.inputNumbers.push(keys.map(k => ''));
                }
            }

            if (this.inputNumbers.length === 0) {
                this.inputNumbers.push("");
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
