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
                answerTypeOptions: [],
                keyForValues: {}
            }
        },
        computed: {},

        methods: {
            handleClick() {
                this.showNextButton = true;
            },

            handleAnswers() {

         /*       var keyValuePair = {},
                    i,
                    keys = this.answerTypeOptions.map(option => option.key),
                    values = this.checkedAnswers,
                    length = values.length;

                for (i = 0; i < length; i++) {
                    keyValuePair[keys[i]] = values[i];
                }

                console.log(keys, values);
                console.log({keyValuePair});*/

                var answerData = JSON.stringify(this.checkedAnswers);

                axios.post('/save-answer', {
                    user_id: this.userId,
                    survey_instance_id: this.surveyInstanceId[0],
                    question_id: this.question.id,
                    question_type_answer_id: 0,
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
            this.answerTypeOptions.push(...options);
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