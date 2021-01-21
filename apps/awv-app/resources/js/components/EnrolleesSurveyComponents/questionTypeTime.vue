<template>
    <div class="scroll-container">
        <!--question without sub_parts-->
        <template v-if="!questionHasSubParts">

            <div v-for="(placeholder, index) in placeholderForSingleQuestion"
                 class="row no-gutters">
                <div class="col-md-12 active">
                    <label v-if="singleTitle" class="label">{{singleTitle}}</label><br>
                    <input type="time"
                           class="time-field"
                           v-model="inputHasText[index]"
                           :disabled="readOnly"
                           :placeholder="placeholder"/>
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
                     class="time-field"
                     :class="{ 'col-md-12': innerIndex === 0,'col-md-6': innerIndex !== 0, 'active': subPart.active }"
                     :key="innerIndex">
                    <label class="label">{{subPart.title}}</label><br>
                    <input type="time"
                           class="text-field"
                           v-model="subPart.value"
                           :placeholder="subPart.placeholder"
                           :disabled="readOnly || !isActive">
                </div>

                <br/>
                <br/>
            </div>
        </template>
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
                {{isLastQuestion ? 'Complete' : 'Next'}}
                <mdb-icon v-show="waiting" icon="spinner" :spin="true"/>
            </mdbBtn>
        </div>
    </div>
</template>

<script>
    import {mdbBtn, mdbIcon} from "mdbvue";

    export default {
        name: "questionTypeTime",
        props: ['question', 'enrollmentSurveyPatients', 'isActive', 'onDoneFunc', 'isLastQuestion', 'waiting', 'readOnly'],
        components: {mdbBtn, mdbIcon},

        data() {
            return {
                inputHasText: [],
                questionOptions: [],
                subParts: [],
                showNextButton: false,
                singleTitle: undefined,
                placeholderForSingleQuestion: [],
                isOptional: false
            }
        },

        computed: {
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
                }
            }
        },

        created() {

            this.isOptional = !!this.question.optional;

            const questionOptions = this.question.type.question_type_answers.map(q => q.options);
            this.questionOptions.push(...questionOptions);

            /*sets subQuestions data*/
            if (this.questionHasSubParts) {
                this.addInputFields(this.questionOptions[0].sub_parts);
            }

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

    .time-field {
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
    }

    .time-field > input {
        width: 130px;
        background: transparent;
        border: none;
        padding: 5px;
    }

    @media (max-width: 490px) {
        .time-field {
            padding-left: 9px;
            width: 325px;
        }
    }
</style>
