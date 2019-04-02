<template>
    <div>
        <div class="radio">
            <div class="row">
                <div v-for="answer in possibleAnswers">
                    <label>{{answer.value}}
                        <input id="question"
                               :name="question.id"
                               :value="answer.value"
                               type="radio"
                               @change="handleAnswer(answer.value)">
                    </label>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
    import {EventBus} from "../event-bus";

    export default {
        name: "questionTypeRadio",
        props: ['question'],
        components: {},

        data() {
            return {
                possibleAnswers: this.question.type.question_type_answers,
                questionOrder: this.question.pivot.order,

            }
        },

        computed: {
            questionHasConditions() {
                return this.question.conditions != null;
            },
        },


        methods: {
            handleAnswer(answerVal) {
                EventBus.$emit('showSubQuestions', answerVal, this.questionOrder, this.question.id)
            },
        }
    }
</script>

<style scoped>
    .radio label {
        width: 420px;
        height: 50px;
        border-radius: 5px;
        border: solid 1px #4aa5d2;
        background-color: #ffffff;
        margin-left: .5rem;
        padding-left: 3%;
        padding-top: 3%;
    }

    .radio label > text {
        padding-left: 3px;
    }

    .radio input[type="radio"] {
      /*  display: none;*/

    }

    .radio input[type="radio"]:checked + label {
        background-color: #4aa5d2;
    }
</style>