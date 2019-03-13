<template>
    <div>
        <div class="boxed">

            <input type="radio" id="radioType" name="radioTypeAnswer">

            <label v-for="option in options" for="radioType">{{option.value}}</label>

        </div>
    </div>
</template>

<script>
    export default {
        props:['question'],

        mounted() {
            console.log('Component mounted.')
        },
        data() {
            return {
                options:this.question.type.question_type_answers
            }
        },
    }
</script>

<style scoped>
    .boxed label {
        width: 450px;
        height: 50px;
        border-radius: 5px;
        border: solid 1px #4aa5d2;
        background-color: #ffffff;
    }
    .boxed input[type="radio"] {
        display: none;
    }
    .boxed input[type="radio"]:checked + label {
        background-color: #4aa5d2;
    }
</style>