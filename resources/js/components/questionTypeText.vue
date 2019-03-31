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
                 v-for="subPart in questionSubParts">
                <label class="label" v-if="questionHasSubParts">{{subPart.title}}</label><br>
                <input type="text"
                       class="text-field"
                       name="textTypeAnswer[]"
                       v-model="inputHasText"
                       :placeholder="subPart.placeholder"
                       @change="onInput">
            </div>
        </div>
        <!--add input fields button-->
        <div v-if="canAddInputFields">
            <button type="button"
                    @click="addInputFields"
                    class="btn-primary">
                {{this.addInputFieldsButtonName}}
            </button>
        </div>
        <div v-if="canRemoveInputFields">
            <button type="button"
                    @click="removeInputFields"
                    class="btn-primary">
                {{this.removeInputFieldsButtonName}}
            </button>
        </div>
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
                canRemoveInputFields: false,
                canAddInputFields: false,
                addFields: {
                    title: 'Need to dynamically',
                    placeholder: 'Set these fields',
                },
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

            addInputFields() {
                this.questionSubParts.push(this.addFields);
                this.canRemoveInputFields = true;
                //this.canAddInputFields = false;
            },

            removeInputFields(index) {
                Vue.delete(this.questionSubParts, index);
            },
        },

        created() {
            const questionOptions = this.question.type.question_type_answers.map(q => q.options);
            this.questionOptions.push(...questionOptions);

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
    .text-field{
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
        width: 300px;
        height: 30px;
    }

    .label{
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