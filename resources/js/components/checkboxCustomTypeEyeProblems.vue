<template>
    <div>
        <input id="different-input"
               class="text-field"
               name="textTypeAnswer"
               v-model="eyesProblemInputHasText"
               :placeholder="inputData.placeholder"
               type="text">
        <input type="submit" @click="handleAnswer">
    </div>
</template>

<script>
    import {EventBus} from "../event-bus";

    export default {
        name: "checkboxCustomTypeEyeProblems",

        props: ['eyeProblemsInputData'],
        data() {
            return {
                inputData: this.eyeProblemsInputData[0],
                eyesProblemInputHasText: '',
                showSubmitButton: false,
            }
        },
        computed: {

        },

        methods: {
            handleAnswer() {
                EventBus.$emit('eyesProblemInputValue', this.eyesProblemInputHasText)
            }
        }
    }
</script>

<style scoped>
    input[type="text"] {
        width: 200px;
        height: 20px;
        padding-right: 50px;
    }

    input[type="submit"] {
        margin-left: -50px;
        height: 25px;
        width: 50px;
        background: blue;
        color: white;
        border: 0;
    }
</style>