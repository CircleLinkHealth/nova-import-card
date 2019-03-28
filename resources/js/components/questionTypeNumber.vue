<template>
    <div>
        <input type="number" name="numberTypeAnswer" v-model="inputHasNumber">
        <br>
        <div v-if="inputHasNumber > '1'">
            <button class="next-btn"
                    name="number"
                    id="number"
                    type="submit"
                    value=""
                    @click="handleClick">Next
            </button>
        </div>
    </div>
</template>

<script>
    import {EventBus} from "../event-bus";

    export default {
        name: "questionTypeNumber",

        mounted() {
            console.log('Component mounted.')
        },

        data() {
            return {
                inputHasNumber: '',
            }
        },
        computed: {

        },

        methods: {
            handleClick() {
                EventBus.$emit('handleNumberType');
            },

            onInput(e) {

            },

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