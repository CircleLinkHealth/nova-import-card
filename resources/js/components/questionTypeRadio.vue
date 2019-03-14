<template>
    <div>
        <div class="custom-radio">
            <div class="row">
                <div v-for="answer in posiibleAnswers">
                    <label>{{answer.value}}
                        <input type="radio"
                               id="radioType"
                               name="radioTypeAnswer">
                    </label>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    export default {
        props: ['question'],

        data() {
            return {
                posiibleAnswers: this.question.type.question_type_answers,
            }
        },
    }
</script>

<style scoped>
    .custom-radio label {
        width: 350px;
        height: 50px;
        border-radius: 5px;
        border: solid 1px #4aa5d2;
        background-color: #ffffff;
        margin-left: .5rem;
        padding-left: 4%;
        padding-top: 3%;
    }

    .custom-radio label > text {
        padding-left: 3px;
    }

    .custom-radio input[type="radio"] {
        /*display: none;*/

    }

    .custom-radio input[type="radio"]:checked + label {
        background-color: #4aa5d2;
    }
</style>