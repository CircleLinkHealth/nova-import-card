<template>
    <div>
        <!--question without sub_parts-->
        <div v-if="!questionHasSubParts">
            <input
                    type="text"
                    name="numberTypeAnswer[]"
                    v-model="inputHasNumber"
                    :placeholder="this.questionPlaceHolder"
                    @change="onInput">
        </div>
        <br>
        <!--question with sub_parts-->
        <div v-if="questionHasSubParts"
             v-for="subPart in questionSubParts">
            <input type="number"
                   name="numberTypeAnswer[]"
                   v-model="inputHasNumber"
                   :placeholder="subPart.placeholder"
                   @change="onInput">
        </div>
        <!--next button-->
        <div v-if="inputHasNumber >'1'">
            <button class="next-btn"
                    name="number"
                    id="number"
                    type="submit">Next
            </button>
        </div>
    </div>
</template>

<script>
    import {EventBus} from "../event-bus";

    export default {
        name: "questionTypeNumber",
        props: ['question'],

        mounted() {
            console.log('Component mounted.')
        },

        data() {
            return {
                inputHasNumber: '',
                questionOptions: [],
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
        },

        methods: {
            onInput() {
                EventBus.$emit('handleNumberType');
            },
        },
        created() {
            const questionOptions = this.question.type.question_type_answers.map(q => q.options);
            this.questionOptions.push(...questionOptions);
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
</style>