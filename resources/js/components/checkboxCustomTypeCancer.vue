<template>
    <div>
        <input id="different-input"
               class="text-field"
               name="textTypeAnswer"
               v-model="cancerInputHasText"
               :placeholder="inputData.placeholder"
               type="text">
        <input type="submit" @click="handleAnswer">
    </div>
</template>

<script>
    export default {
        name: "checkboxCustomTypeCancer",
        props: ['cancerInputData'],
        data() {
            return {
                inputData: this.cancerInputData[0],
                cancerInputHasText: '',
                showSubmitButton: false,
            }
        },
        computed: {

            hasTypedFourChars() {
                return this.cancerInputHasText.length > 3 ? this.showSubmitButton = true : this.showSubmitButton = false;
            },
        },

        methods: {
            handleAnswer() {
                //EventBus.$emit('cancerInputValue', this.cancerInputHasText,)
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
