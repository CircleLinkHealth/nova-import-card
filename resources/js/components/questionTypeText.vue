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
                <div v-if="canRemoveInputFields">
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
            <div class="row">
                <div v-for="(subPartArr, index) in subParts">
                    <div v-for="(subPart, innerIndex) in subPartArr" :key="innerIndex" style="margin-left: 15%;">
                        <label class="label">{{subPart.title}}</label><br>
                        <input type="text"
                               class="text-field"
                               v-model="subPart.value"
                               :placeholder="subPart.placeholder"
                               :disabled="!isActive">
                    </div>
                </div>

                <!--add input fields button-->
                <div v-for="extraFieldButtonName in extraFieldButtonNames">
                    <div v-if="canAddInputFields">
                        <button type="button"
                                @click="addInputFields(extraFieldButtonName.sub_parts)"
                                class="btn-add-field">
                            {{extraFieldButtonName.add_extra_answer_text}}
                        </button>
                    </div>
                    <!--remove input fields button-->
                    <div v-if="canRemoveInputFields">
                        <button type="button"
                                @click="removeInputFields()"
                                class="btn-primary">
                            {{extraFieldButtonName.remove_extra_answer_text}}
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <br>
        <!--:disabled="!hasTypedInTwoFields"-->

        <!--next button-->
        <div :class="isLastQuestion ? 'text-center' : 'text-left'">
            <mdbBtn v-show="isActive"
                    color="primary"
                    class="next-btn"
                    name="number"
                    id="number"

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
                extraFieldButtonNames: [],
                canRemoveInputFields: false,
                canAddInputFields: false,
                showNextButton: false,
                placeholderForSingleQuestion: [],
            }
        },

        computed: {
            hasTypedInTwoFields() {
                return this.inputHasText.length > 1;
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
                    return 0;
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
                this.canRemoveInputFields = true;
            },

            addInputFields(extraFieldSubParts) {

                const subQuestions = extraFieldSubParts;
                // this.subParts.push(...subQuestions);
                const fields = subQuestions.map(x => {
                    return Object.assign({}, x, {value: ''});
                });
                this.subParts.push(fields);

                /*
                const subParts = extraFieldSubParts.map(q => q);
                for (let j = 0; j < subParts.length; j++) {
                    const subPart = subParts[j];
                    this.subParts.push({
                        title: subPart.title,
                        placeholder: subPart.placeholder,
                        key: subPart.key
                    });
                }
                */

                this.canRemoveInputFields = true;

            },
            /*@todo:delete answer also*/
            removeInputFields(index) {//index is undefined. if it is defined it doesn't work. Can anyone clarify pls?
                // this.subParts.splice(index, 2);
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
            if (this.hasAnswerType) {
                return this.questionOptions[0].allow_multiple === true ? this.canAddInputFields = true : this.canAddInputFields = false;
            }
            return false;
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

    .btn-add-field {
        width: 271px;
        height: 40px;
        font-family: Poppins;
        font-size: 24px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.3px;
        color: #50b2e2;

    }

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
        font-family: Poppins;
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
</style>