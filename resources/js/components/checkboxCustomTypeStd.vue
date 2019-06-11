<template>
    <div>
        <input id="different-input"
               class="text-field"
               name="textTypeAnswer"
               v-model="sdtProblemInputHasText"
               :placeholder="inputData.placeholder"
               type="text">
        <input type="submit" @click="handleAnswer">
    </div>
</template>

<script>

    import {EventBus} from "../event-bus";

    export default {
        name: "checkboxCustomTypeStd",
        props: ['stdProblemsInputData'],
        data() {
            return {
                inputData: this.stdProblemsInputData[0],
                sdtProblemInputHasText: '',
                showSubmitButton: false,
            }
        },
        computed: {

        },

        methods: {
            handleAnswer() {
                EventBus.$emit('sdtProblemInputValue', this.sdtProblemInputHasText)
            }
        }
    }
</script>

<style scoped>

</style>