<template>
    <div>
        <!--question without sub_parts-->
        <template v-if="!questionHasSubParts">

            <div v-for="(placeholder, index) in placeholderForSingleQuestion">
                <input type="text"
                       class="text-field"
                       v-model="inputHasText[index]"
                       :placeholder="placeholder">
            </div>

            <br/>

            <!--add single input fields button-->
            <div v-for="extraFieldButtonName in extraFieldButtonNames">
                <div v-if="canAddInputFields">
                    <button type="button"
                            @click="addInputField(extraFieldButtonName.placeholder)"
                            class="btn-add-field">
                        {{extraFieldButtonName.add_extra_answer_text}}
                    </button>
                </div>
                <!--add remove input fields button-->
                <div v-if="placeholderForSingleQuestion.length > 1">
                    <button type="button"
                            @click="removeSingleInputFields()"
                            class="btn-primary">
                        {{extraFieldButtonName.remove_extra_answer_text}}
                    </button>
                </div>
            </div>

        </template>
        <template v-else>

            <!--question with sub_parts-->
            <div class="row"
                 v-for="(subPartArr, index) in subParts">
                <div v-for="(subPart, innerIndex) in subPartArr"
                     :class="subPartsClass"
                     :key="innerIndex">
                    <label class="label">{{subPart.title}}</label><br>
                    <input type="text"
                           class="text-field"
                           v-model="subPart.value"
                           :placeholder="subPart.placeholder"
                           :disabled="!isActive">
                </div>

                <!--remove input fields button-->
                <div v-if="subParts.length > 1"
                     class="col-md-12"
                     v-for="extraFieldButtonName in extraFieldButtonNames">
                    <span @click="removeInputFields(index)"
                          class="button-text-only remove">
                        {{extraFieldButtonName.remove_extra_answer_text}}
                    </span>
                </div>

                <br/>
                <br/>
            </div>

            <br/>

            <!--add input fields button-->
            <div class="row" v-if="canAddInputFields">
                <div v-for="extraFieldButtonName in extraFieldButtonNames"
                     class="col-md-12">
                    <span class="button-text-only"
                          @click="addInputFields(extraFieldButtonName.sub_parts)">
                        {{extraFieldButtonName.add_extra_answer_text}}
                    </span>
                </div>
            </div>


        </template>

        <br>
        <!---->

        <!--next button-->
        <div :class="isLastQuestion ? 'text-center' : 'text-left'">
            <mdbBtn v-show="isActive"
                    color="primary"
                    class="next-btn"
                    name="number"
                    id="number"
                    :disabled="!(hasTypedInTwoFields || hasTypedInSubParts)"
                    @click="handleAnswer()">
                {{isLastQuestion ? 'Complete' : 'Next'}}
                <font-awesome-icon v-show="waiting" icon="spinner" :spin="true"/>
            </mdbBtn>
        </div>
    </div>
</template>

<script>

    import {mdbBtn} from 'mdbvue';
    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faSpinner} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

    library.add(faSpinner);

    export default {
        name: "questionTypeText",
        props: ['question', 'userId', 'surveyInstanceId', 'isActive', 'isSubQuestion', 'onDoneFunc', 'isLastQuestion', 'waiting'],
        components: {mdbBtn, FontAwesomeIcon},


        data() {
            return {
                inputHasText: [],
                questionOptions: [],
                subParts: [],
                subPartsClass: 'col-md-6',
                extraFieldButtonNames: [],
                canAddInputFields: false,
                showNextButton: false,
                placeholderForSingleQuestion: [],
            }
        },
        computed: {
            hasTypedTwoNumbers() {
                return this.inputHasText.length > 1;
            },

            hasTypedInTwoFields() {
                return this.inputHasText.length > 1;
            },

            hasTypedInSubParts() {
                return this.subParts.length && this.subParts[0][0].value.length > 1 && this.subParts[0][1].value.length > 1;
            },

            hasTypedTwoCharacters() {
                var text = this.inputHasText;
                const length = text.map(q => q.length);
                return length > 1 === true;
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
                    return this.questionOptions[0].hasOwnProperty('sub_parts');
                }
                return false;
            },

            questionHasPlaceHolderInSubParts() {
                if (this.questionHasSubParts) {
                    const placeholder = this.questionOptions[0].sub_parts.map(q => q.placeholder);
                    if (placeholder) {
                        return true;
                    }
                }
                return false;
            },

            questionHasPlaceHolderInOptions() {
                if (!this.questionHasPlaceHolderInSubParts && this.questionOptions.length !== 0) {
                    const placeholder = this.questionOptions[0].placeholder;
                    if (placeholder) {
                        return true;
                    }
                }
                return false;
            },

            addInputFieldsButtonName() {
                if (this.hasAnswerType) {
                    return this.questionOptions[0].add_extra_answer_text;
                }
                return '';

            },

            removeInputFieldsButtonName() {
                if (this.hasAnswerType) {
                    return this.questionOptions[0].remove_extra_answer_text;
                }
                return '';
            },

            conditions() {
                if (this.hasTypedInTwoFields && this.questionHasSubParts) {
                    return true;
                } else {
                    if (this.hasTypedTwoCharacters && !this.questionHasSubParts) {
                        return true;
                    }
                }
            }

        },

        mounted() {
            /*get placeholder for single question input*/
            if (this.questionHasPlaceHolderInSubParts && !this.questionHasPlaceHolderInOptions) {
                const placeholder = this.questionOptions[0].sub_parts.map(q => q.placeholder);
                this.placeholderForSingleQuestion.push(...placeholder);
            }
            if (this.questionHasPlaceHolderInOptions) {
                const placeholder2 = this.questionOptions[0].placeholder;
                this.placeholderForSingleQuestion.push(placeholder2);
            }
        },

        methods: {

            addInputField(placeholder) {
                this.placeholderForSingleQuestion.push(placeholder);
            },

            addInputFields(extraFieldSubParts, values) {

                if (values) {
                    values.forEach((v) => {
                        const fields = extraFieldSubParts.map((x) => {
                            return Object.assign({}, x, {value: v[x.key] || ''});
                        });
                        this.subParts.push(fields);
                    });

                }
                else {
                    const fields = extraFieldSubParts.map((x) => {
                        return Object.assign({}, x, {value: ''});
                    });
                    this.subParts.push(fields);
                }

            },

            removeInputFields(index) {
                this.subParts.splice(index, 1);
            },

            removeSingleInputFields(index) {
                this.placeholderForSingleQuestion.splice(index, 1)
            },

            handleAnswer() {
                const answer = [];
                if (this.subParts.length === 0) {
                    for (let j = 0; j < this.inputHasText.length; j++) {
                        var values = {
                            name: this.inputHasText[j]
                        };
                        answer.push(values);
                    }
                } else {
                    this.subParts.forEach(subPart => {
                        //arr => [{key, value}, {key, value}]
                        const obj = {};
                        subPart.forEach(p => {
                            obj[p.key] = p.value;
                        });
                        answer.push(obj);
                    })
                }

                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer);
            }
        },

        created() {
            const questionOptions = this.question.type.question_type_answers.map(q => q.options);
            this.questionOptions.push(...questionOptions);

            if (this.hasAnswerType) {
                const buttonNames = questionOptions.map(q => q);
                this.extraFieldButtonNames.push(...buttonNames);
            }

            /*sets subQuestions data*/
            if (this.questionHasSubParts) {
                this.addInputFields(this.questionOptions[0].sub_parts);
            }

            /*sets canAddInputField data*/
            this.canAddInputFields = this.hasAnswerType && this.questionOptions[0].allow_multiple;

            if (this.question.answer && this.question.answer.value) {
                if (this.questionHasSubParts) {
                    this.subParts = [];
                    this.addInputFields(this.questionOptions[0].sub_parts, this.question.answer.value);
                }
                else {
                    //todo
                }
            }

        },
    }
</script>

<style scoped>

    .text-field {
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
        width: 300px;
        height: 30px;
    }

    .label {
        width: 64px;
        height: 40px;
        font-family: Poppins, serif;
        font-size: 20px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.3px;
        color: #1a1a1a;
    }

    .btn-primary {
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .btn-primary.disabled {
        opacity: 50%;
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .fa, .fas {
        color: #50b2e2;
        font-weight: unset;
    }

    span.button-text-only {
        cursor: pointer;
        font-family: Poppins, serif;
        font-size: 24px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.33px;
        color: #50b2e2;
    }

    span.button-text-only.remove {
        color: #ff6e6e;
    }
</style>
