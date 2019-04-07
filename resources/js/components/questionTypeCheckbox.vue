<template>
    <div class="custom-checkbox">
        <div v-for="checkBox in checkBoxValues">
            <label>{{checkBox.value}}
                <input class="check-box"
                       type="checkbox"
                       name="checkboxTypeAnswer"
                       :value="checkBox.value"
                       v-model="checkedAnswers"
                       @click="showNextButton = true">
            </label>

            <!--next button-->
            <div v-if="showNextButton">
                <button class="next-btn"
                        name="text"
                        id="text"
                        type="submit"
                        @click="handleAnswers(checkBox.options.key)">Next
                </button>
            </div>
        </div>


    </div>
</template>

<script>

    export default {
        name: "questionTypeCheckbox",
        props: ['question', 'userId', 'surveyInstanceId'],
        components: {},

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers,
                showNextButton: false,
                checkedAnswers: [],
            }
        },
        computed: {},

        methods: {
            handleAnswers(key) {
                var answer = {};
                answer[key] = this.checkedAnswers;

                console.log(answer);
                var answerData = JSON.stringify(answer);

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
                    });
            }
        },

        created() {

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