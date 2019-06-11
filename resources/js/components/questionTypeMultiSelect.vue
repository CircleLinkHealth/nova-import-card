<template>
    <div>
        <div class="row">
            <div v-for="(answer, index) in lastQuestionAnswer">
                <label>{{answer.name}}</label>
                <div v-for="(checkBoxOption, index) in multiSelectOptions">
                    <label>
                        <input class="multi-select"
                               type="checkbox"
                               name="checkboxTypeAnswer"
                               v-model="checkedAnswers[checkBoxOption]"
                               @click="handleClick()">
                        {{checkBoxOption}}
                    </label>
                </div>
            </div>
        </div>
    </div>

</template>

<script>

    export default {
        name: "questionTypeMultiSelect",
        props: ['question', 'questions', 'userId', 'surveyInstanceId', 'surveyAnswers'],
        components: {},

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers,
                checkBoxOptions: [],
                multiSelectOptions: [],
                lastQuestionAnswer: [],
                checkedAnswers: [],

            }
        },
        computed: {
            placeHolder() {
                return this.checkBoxOptions[0].placeholder
            },

            lastQuestionOrderNumber() {
                const lastQuestionOrder = this.checkBoxOptions[0].import_answers_from_question.question_order;
                this.lastQuestionAnswers(lastQuestionOrder);
                return lastQuestionOrder;
            }


        },

        methods: {//@todo:fix this
            lastQuestionAnswers(lastQuestionOrder) {
                const id = this.questions.filter(function (q) {
                    return q.pivot.order === lastQuestionOrder && q.pivot.sub_order === null;
                })[0].pivot.question_id;

                const lastAnswerValues = this.surveyAnswers.filter(function (q) {
                    return q.id === id;
                })[0].value;

                this.lastQuestionAnswer.push(...JSON.parse(lastAnswerValues));
            },
            handleClick() {
                this.handleAnswers();
            },
            handleAnswers() {
                const answer = [];
                for (let j = 0; j < this.checkedAnswers.length; j++) {
                    const val = this.checkedAnswers[j];
                    const q = this.multiSelectOptions.find(x => x.value === val);
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
        mounted() {


        },

        created() {
            const options = this.question.type.question_type_answers.map(q => q.options);
            const multiSelect = options.flatMap(q => q.multi_select_options);

            this.checkBoxOptions.push(...options);
            this.multiSelectOptions.push(...multiSelect);


        },
    }
</script>

<style scoped>
    .checkbox-dropdown {
        width: 500px;
        height: 350px;
        border: solid 1px #f2f2f2;
        background-color: #ffffff;
    }

    .checkbox-dropdown label {
        /*  width: 54px;
          height: 29px;
          font-family: Poppins;
          font-size: 16px;
          font-weight: 400;
          font-style: normal;
          font-stretch: normal;
          line-height: normal;
          letter-spacing: 1px;
          color: #1a1a1a;*/
    }
</style>