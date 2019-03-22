<template>
    <div>
        <div class="custom-radio">
            <div class="row">
                <div v-for="answer in possibleAnswers">
                    <label>{{answer.value}}
                        <input type="radio"
                               id="radioType"
                               name="radioTypeAnswer"
                               v-model="checkedAnswer"
                               :value="answer.value"
                               @click="handleAnswer">
                    </label>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import {bus} from "../app";

    export default {
        props: ['question'],

        data() {
            return {
                possibleAnswers: this.question.type.question_type_answers,
                checkedAnswer: [],
            }
        },

        methods: {
            handleAnswer() {
                console.log(this.checkedAnswer)
            },
        }
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