<template>
    <div>
        <!--question without sub_parts-->
        <div v-if="!questionHasSubParts">
            <input
                    type="text"
                    class="text-field"
                    name="textTypeAnswer[]"
                    v-model="inputHasText"
                    :placeholder="this.questionPlaceHolder"
                    @change="onInput">
        </div>

        <br>
        <!--question with sub_parts-->
        <div class="row">
            <div v-if="questionHasSubParts"
                 v-for="(subQuestion, index) in subQuestions" :key="index" style="margin-left: 15%;">
                <label class="label" v-if="questionHasSubParts">{{subQuestion.title}}</label><br>
                <input type="text"
                       class="text-field"
                       name="textTypeAnswer[]"
                       v-model="inputHasText[subQuestion.title]"
                       :placeholder="subQuestion.placeholder"
                       @change="onInput()">


                <div v-for="extraFieldButtonName in extraFieldButtonNames">
                    <div v-if="canAddInputFields">
                        <button type="button"
                                @click="addInputFields(subQuestion.title, subQuestion.placeholder)"
                                class="btn-primary">
                            {{extraFieldButtonName.add_extra_answer_text}}
                        </button>
                    </div>
                    <div v-if="canRemoveInputFields">
                        <button type="button"
                                @click="removeInputFields()"
                                class="btn-primary">
                            {{extraFieldButtonName.remove_extra_answer_text}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!--add input fields button-->

        <!--next button-->
        <div v-if="inputHasText >'1'">
            <button class="next-btn"
                    name="text"
                    id="text"
                    type="submit">Next
            </button>
        </div>
    </div>
</template>

<script>

    import {EventBus} from "../event-bus";

    export default {
        name: "questionTypeText",
        props: ['question'],

        mounted() {

        },

        data() {
            return {
                inputHasText: [],
                questionOptions: [],
                subQuestions: [],
                extraFieldButtonNames: [],
                canRemoveInputFields: false,
                canAddInputFields: false,
            }
        },
        computed: {
            hasAnswerType() {
                return this.question.type.question_type_answers.length !== 0;
            },

            questionHasSubParts() {
                if (this.hasAnswerType) {
                    return this.questionOptions[0].hasOwnProperty('sub_parts');
                }
                return false;
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

        methods: {
            onInput() {
                EventBus.$emit('handleTextType');
            },

            addInputFields(label, placeholder, index) {
                this.subQuestions.push({
                    title: label,
                    placeholder: placeholder
                });

                this.canRemoveInputFields = true;
                //this.canAddInputFields = false;
            },

            removeInputFields(index) {
                this.delete(this.questionSubParts, index);
            },
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
                const subQuestions = this.questionOptions[0].sub_parts;
                this.subQuestions.push(...subQuestions);
            }
            /*sets canAddInputField data*/
            if (this.hasAnswerType) {
                return this.questionOptions[0].allow_multiple === true ? this.canAddInputFields = true : '';
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
</style>