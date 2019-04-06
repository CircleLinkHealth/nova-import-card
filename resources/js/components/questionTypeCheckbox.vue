<template>
    <div class="custom-checkbox">
        <div v-for="checkBox in checkBoxValues">
            <label>{{checkBox.value}}
                <input class="check-box"
                       type="checkbox"
                       name="checkboxTypeAnswer"
                       @click="collectAnswers(checkBox.value)">
            </label>
        </div>

        <!--next button-->
        <div v-if="wasClicked">
            <button class="next-btn"
                    name="text"
                    id="text"
                    type="submit"
                    @click="handleAnswers">Next
            </button>
        </div>
    </div>
</template>

<script>

    import {EventBus} from "../event-bus";

    export default {
        name: "questionTypeCheckbox",
        props: ['question', 'userId', 'surveyInstanceId'],
        components: {},

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers,
                wasClicked: false,
                clickedAnswers: [],
            }
        },
        computed: {

        },

        methods: {
            collectAnswers(answerVal) {
                EventBus.$emit('handleTextType', answerVal);
                this.wasClicked = true;
            },
            handleAnswers(){
                var answer = this.clickedAnswers;
                var answerData = JSON.stringify({answer});
                axios.post('/save-answer', {
                    user_id: this.userId,
                    survey_instance_id: this.surveyInstanceId[0],
                    question_id: this.question.id,
                    question_type_answer_id: 0,
                    value_1: answerData,
                })
                    .then(function (response) {
                        console.log(response);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });}
        },

        created() {
            EventBus.$on('handleTextType', (answerVal) => {
                this.clickedAnswers.push(answerVal)
            });
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