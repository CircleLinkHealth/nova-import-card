<template>
    <div>
        <!--question without sub_parts-->
        <div v-if="!questionHasSubParts">
            <input
                    type="text"
                    name="textTypeAnswer[]"
                    v-model="inputHasText"
                    :placeholder="placeholderValue"
                    @change="onInput">
        </div>
        <br>
        <!--question with sub_parts-->
        <div v-if="questionHasSubParts"
             v-for="subPart in subParts">
            <label v-if="questionHasSubParts">{{subPart.title}}</label>
            <input
                    type="text"
                    name="textTypeAnswer[]"
                    v-model="inputHasText"
                    :placeholder="subPart.placeholder"
                    @change="onInput">
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
                placeholderValue: this.question.type.question_type_answers[0].options.placeholder,
                subParts: [],

            }
        },
        computed: {
            questionHasSubParts() {
                return this.subParts != null;
            }
        },

        methods: {
            onInput() {
                EventBus.$emit('handleTextType');
            },
        },

        created() {
            const questionSubParts = this.question.type.question_type_answers[0].options.sub_parts;
            this.subParts.push(...questionSubParts);

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