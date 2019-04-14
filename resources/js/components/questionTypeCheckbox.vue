<template>
    <div class="custom-checkbox">
        <div v-for="checkBox in checkBoxValues">
            <label>{{checkBox.value}}
                <input class="check-box"
                       type="checkbox"
                       name="checkboxTypeAnswer"
                       :value="checkBox.value"
                       v-model="checkedAnswers"
                       @click="handleClick">
            </label>

        </div>
        <!--next button-->
        <div v-if="showNextButton">
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

    export default {
        name: "questionTypeCheckbox",
        props: ['question', 'userId', 'surveyInstanceId'],
        components: {},

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers,
                showNextButton: false,
                checkedAnswers: [],
                questionOptions: [],
                keyForValues: {},
                showDifferentInput: false,
            }
        },
        computed: {

            hasAnswerType() {
                return this.question.type.question_type_answers.length !== 0;
            },

            questionTypeAnswerId() {
                if (this.hasAnswerType) {
                    return this.question.type.question_type_answers[0].id;
                } else {
                    return 0;
                }
            },

        },

        methods: {
            handleClick() {
                this.showNextButton = true;
            },

            handleAnswers() {
                const answer = [];
                for (let j = 0; j < this.checkedAnswers.length; j++) {
                        const val = this.checkedAnswers[j];
                        const q = this.checkBoxValues.find(x => x.value === val);
                        answer.push({[q.options.key]: val});
                }



                var answerData = JSON.stringify(answer);

                axios.post('/save-answer', {
                    user_id: this.userId,
                    survey_instance_id: this.surveyInstanceId[0],
                    question_id: this.question.id,
                    question_type_answer_id: this.questionTypeAnswerId,
                    value: answerData,
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
            const options = this.checkBoxValues.map(checkBoxValue => checkBoxValue.options);
            this.questionOptions.push(...options);
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