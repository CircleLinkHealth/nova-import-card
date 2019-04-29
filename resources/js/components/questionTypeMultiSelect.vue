<template>
    <div>
        <div class="row">
            <div v-for="answer in lastQuestionAnswer">
                <div>
                    {{answer.name}}
                </div>
                 <div v-for="checkBoxOption in multiSelectOptions">
                      <label>
                          <input class="multi-select"
                                 type="checkbox"
                                 name="checkboxTypeAnswer">
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
        props: ['question', 'questions', 'userId', 'surveyInstanceId'],
        components: {},

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers[0].value,
                checkBoxOptions: [],
                multiSelectOptions: [],
                lastQuestionAnswer: [],
            }
        },
        computed: {
            placeHolder() {
                return this.checkBoxOptions[0].placeholder
            },

            lastQuestionOrderNumber() {
                const lastQuestionOrder = this.checkBoxOptions[0].import_answers_from_question.question_order;
                this.lastQuestion(lastQuestionOrder);
                return lastQuestionOrder;
            }


        },

        methods: {
            lastQuestion(lastQuestionOrder) {
                /*const lastQuestionOrder = this.checkBoxOptions[0].import_answers_from_question.question_order;*/
                const id = this.questions.filter(function (q) {
                    return q.pivot.order === lastQuestionOrder && q.pivot.sub_order === null;
                })[0].pivot.question_id;

                axios.get('get-previous-answer/' + id + '/' + this.userId)
                    .then(response => {
                        if (response.data.previousQuestionAnswer.length !== 0) {
                            this.lastQuestionAnswer = JSON.parse(response.data.previousQuestionAnswer);
                            console.log(response)
                        } else
                            this.lastQuestionAnswer = '';
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            },


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