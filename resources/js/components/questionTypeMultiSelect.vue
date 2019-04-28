<template>
    <div>
        <div class="row">
            <div class="checkbox-dropdown col-lg-4" v-for="answer in lastQuestionAnswer">
                {{answer.name}}
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

            lastQuestionId() {
                const lastQuestionOrder =  this.checkBoxOptions[0].import_answers_from_question.question_order;
                return this.questions.filter(function (q) {
                    return q.pivot.order === lastQuestionOrder && q.pivot.sub_order === null;
                })[0].pivot.question_id;
            },

            lastAnswerValue() {
                axios.get('get-previous-answer/' + this.lastQuestionId + '/' + this.userId)
                    .then(response => {
                        this.lastQuestionAnswer = response.data.previousQuestionAnswer;
                        console.log(response)
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                if (this.lastQuestionAnswer !== null) {
                    return this.lastQuestionAnswer;
                }
                return '';

            }


        },

        methods: {
            dropdown() {

            },


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