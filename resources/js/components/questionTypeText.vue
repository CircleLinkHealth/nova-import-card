<template>
   <div>
       <input type="text" name="textTypeAnswer[]" v-model="inputHasText" @change="onInput">
       <br>
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

        mounted() {

        },

        data() {
            return {
                inputHasText: [],
            }
        },
        computed: {},

        methods: {
            onInput() {
                EventBus.$emit('handleTextType');
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