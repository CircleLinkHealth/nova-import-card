<template>
    <div class="scroll-container">
        <div class="scrollable">

            <!--question without sub_parts-->
            <template v-if="!questionHasSubParts">

                <div v-for="(placeholder, index) in placeholderForSingleQuestion"
                     class="row no-gutters">
                    <div class="col-md-12 active">
                        <label v-if="singleTitle" class="label">{{singleTitle}}</label><br>
                        <input type="text"
                               class="text-field margin-bottom-10"
                               v-model="inputHasText[index]"
                               :disabled="readOnly"
                               :placeholder="placeholder"/>
                    </div>
                    <!--remove input fields button-->
                    <div v-if="!readOnly && placeholderForSingleQuestion.length > 1"
                         class="col-md-12"
                         v-for="extraFieldButtonName in extraFieldButtonNames">
                        <div @click="removeSingleInputFields(index)"
                             class="button-text-only remove">
                            <mdb-icon icon="minus-circle"/>
                            {{extraFieldButtonName.remove_extra_answer_text}}
                        </div>
                    </div>
                </div>

                <!--add single input fields button-->
                <div class="row no-gutters" v-if="!readOnly && canAddInputFields">
                    <div v-for="extraFieldButtonName in extraFieldButtonNames"
                         class="col-md-12">
                    <span class="button-text-only"
                          @click="addInputField(extraFieldButtonName.placeholder)">
                          <mdb-icon icon="plus-circle"/> {{extraFieldButtonName.add_extra_answer_text}}
                    </span>
                    </div>
                </div>

            </template>
            <template v-else>

                <!--question with sub_parts-->
                <!-- css class is like that to cover case of question 19 in HRA -->
                <div class="row no-gutters"
                     v-for="(subPartArr, index) in subParts">
                    <div v-for="(subPart, innerIndex) in subPartArr"
                         @click="onSubPartClick(index, innerIndex)"
                         class="sub-part"
                         :class="{ 'col-md-12': innerIndex === 0,'col-md-6': innerIndex !== 0, 'active': subPart.active }"
                         :key="innerIndex">
                        <label class="label">{{subPart.title}}</label><br>
                        <input type="text"
                               class="text-field"
                               v-model="subPart.value"
                               :placeholder="subPart.placeholder"
                               :disabled="readOnly || !isActive">
                    </div>

                    <!--remove input fields button-->
                    <div v-if="!readOnly && subParts.length > 1"
                         class="col-md-12"
                         v-for="extraFieldButtonName in extraFieldButtonNames">
                        <div @click="removeInputFields(index)"
                             class="button-text-only remove">
                            <mdb-icon icon="minus-circle"/>
                            {{extraFieldButtonName.remove_extra_answer_text}}
                        </div>
                    </div>

                    <br/>
                    <br/>
                </div>

                <br/>

                <!--add input fields button-->
                <div class="row no-gutters" v-if="!readOnly && canAddInputFields">
                    <div v-for="extraFieldButtonName in extraFieldButtonNames"
                         class="col-md-12">
                    <span class="button-text-only"
                          @click="addInputFields(extraFieldButtonName.sub_parts)">
                          <mdb-icon icon="plus-circle"/> {{extraFieldButtonName.add_extra_answer_text}}
                    </span>
                    </div>
                </div>


            </template>

        </div>

        <br>

        <!--next button-->
        <div :class="isLastQuestion ? 'text-center' : 'text-left'">
            <mdbBtn v-show="!readOnly && isActive"
                    color="primary"
                    class="next-btn"
                    name="number"
                    id="number"
                    :disabled="!(isOptional ||hasTypedInAllInputs || hasTypedInSubParts)"
                    @click="handleAnswer()">
                {{buttonText}}
                <mdb-icon v-show="waiting" icon="spinner" :spin="true"/>
            </mdbBtn>
        </div>
    </div>
</template>

<script>

    import {mdbBtn, mdbIcon} from 'mdbvue';

    const SINGLE_INPUT_KEY_NAME = "name";

    export default {
        name: "questionTypeText",
        props: ['question', 'isActive', 'isSubQuestion', 'onDoneFunc', 'isLastQuestion', 'waiting', 'readOnly'],
        components: {mdbBtn, mdbIcon},

        data() {
            return {
                inputHasText: [],
                questionOptions: [],
                subParts: [],
                extraFieldButtonNames: [],
                canAddInputFields: false,
                showNextButton: false,
                singleTitle: undefined,
                placeholderForSingleQuestion: [],
                isOptional: false
            }
        },
        computed: {
            buttonText(){
                if (this.isLastQuestion){
                    return 'Complete';
                }
                if (this.question.optional && this.inputHasText[0] === ''){
                    return 'Skip';
                }
                return 'Next';
            },

            hasTypedInAllInputs() {
                return this.inputHasText.length > 0 && this.inputHasText.every(t => t.length > 0);
            },

            hasTypedInSubParts() {
                return this.subParts.length && this.subParts[0][0].value.length > 0 && this.subParts[0][1].value.length > 0;
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

        },

        mounted() {
        },

        methods: {

            onSubPartClick(index, innerIndex) {
                this.subParts.forEach((coll, i) => {
                    coll.forEach((s, ii) => {
                        s.active = index === i && innerIndex === ii;
                    });
                });
            },

            addInputField(placeholder, value) {
                this.inputHasText.push(value || "");
                this.placeholderForSingleQuestion.push(placeholder);
            },

            addInputFields(extraFieldSubParts, values) {

                if (values) {
                    values.forEach((v) => {
                        const fields = extraFieldSubParts.map((x) => {
                            return Object.assign({}, x, {value: v[x.key] || '', active: false});
                        });
                        this.subParts.push(fields);
                    });

                } else {
                    const fields = extraFieldSubParts.map((x) => {
                        return Object.assign({}, x, {value: '', active: false});
                    });
                    this.subParts.push(fields);
                }
            },

            removeInputFields(index) {
                this.subParts.splice(index, 1);
            },

            removeSingleInputFields(index) {
                this.placeholderForSingleQuestion.splice(index, 1);
                this.inputHasText.splice(index, 1);
            },

            handleAnswer() {
                const answer = [];
                if (this.subParts.length === 0) {
                    for (let j = 0; j < this.inputHasText.length; j++) {
                        const values = {};
                        values[SINGLE_INPUT_KEY_NAME] = this.inputHasText[j];
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

                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer, this.isLastQuestion);
            },

            setInputFieldsFromServer(value) {
                if (this.questionHasSubParts) {
                    this.subParts = [];
                    this.addInputFields(this.questionOptions[0].sub_parts, value);
                } else {
                    let placeholder = "";
                    if (this.extraFieldButtonNames && this.extraFieldButtonNames.length) {
                        placeholder = this.extraFieldButtonNames[0].placeholder;
                    }

                    value.forEach(answer => {
                        this.addInputField(placeholder, answer[SINGLE_INPUT_KEY_NAME]);
                    });
                }
            }
        },

        created() {

            this.isOptional = !!this.question.optional;

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

            this.singleTitle = this.questionOptions && this.questionOptions.length && this.questionOptions[0].title;

            if (this.question.answer && this.question.answer.value) {
                this.setInputFieldsFromServer(this.question.answer.value);
            } else if (this.question.answer && this.question.answer.suggested_value) {
                this.setInputFieldsFromServer(this.question.answer.suggested_value);
            } else {
                /*get placeholder for single question input*/
                if (this.questionHasPlaceHolderInSubParts && !this.questionHasPlaceHolderInOptions) {
                    const placeholder = this.questionOptions[0].sub_parts.map(q => q.placeholder);
                    placeholder.forEach(p => this.addInputField(p));
                }
                if (this.questionHasPlaceHolderInOptions) {
                    const placeholder2 = this.questionOptions[0].placeholder;
                    this.addInputField(placeholder2);
                }
            }

        },
    }
</script>

<style scoped lang="scss">

    $primary-color-active: #4aa5d2;

    .sub-part {
        margin-bottom: 20px;
    }

    .label {
        font-family: Poppins, serif;
        font-size: 24px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.33px;
        color: #d5dadd;
        margin-bottom: 20px;
    }

    ::placeholder {
        color: #d5dadd;
    }

    .active > .label, .active > ::placeholder {
        color: #1a1a1a;
    }

    .btn-primary {
        /*background-color: #50b2e2;*/
        /*border-color: #4aa5d2;*/
    }

    .btn-primary.disabled {
        opacity: 50%;
        /*background-color: #50b2e2;*/
        /*border-color: #4aa5d2;*/
    }

    .text-field {
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
        width: 100%;
        height: 30px;
        font-size: 24px;
        letter-spacing: 1.33px;
    }

    .text-field:active, .text-field:focus, .text-field:hover {
        border-color: #4aa5d2;
    }

    .text-field[disabled="disabled"] {
        opacity: 50%;
    }

    .input.text-field.active {
        border: none;
        border-bottom: 1px solid $primary-color-active;
    }

    .btn.btn-primary.radio.active,
    input.text-field.active,
    input.text-field.active::placeholder {
        background-color: $primary-color-active;
        color: #ffffff;
    }

    input.text-field:hover {
        border: none;
        border-bottom: 1px solid $primary-color-active;
    }

    .btn.btn-primary.radio:hover {
        border: 1px solid $primary-color-active;
    }

    .button-text-only {
        cursor: pointer;
        font-family: Poppins, serif;
        font-size: 24px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.33px;
        color: #50b2e2;
        margin-bottom: 20px;
    }

    .button-text-only.remove {
        color: #ff6e6e;
    }

    @media (max-width: 490px) {
        .label, .text-field {
            font-size: 16px;
        }

        .sub-part {
            padding-left: 9px;
            font-size: 15px;
        }

        .no-gutters {
            padding-left: 9px;
            font-size: 15px;
        }

        .button-text-only {
            font-size: initial;
        }
    }

    @media (max-width: 996px) {
        .label {
            font-size: 100%;
        }
    }
</style>

