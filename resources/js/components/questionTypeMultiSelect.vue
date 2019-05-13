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
                               v-model="checkedAnswers[index]"
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
        props: ['question', 'userId', 'surveyInstanceId', 'isActive', 'isSubQuestion', 'onDoneFunc', 'isLastQuestion', 'waiting', 'surveyAnswers'],
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
            },

            questionTypeAnswerId() {
                if (this.hasAnswerType) {
                    return this.question.type.question_type_answers[0].id;
                } else {
                    return 0;
                }
            },


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
                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer);
            }


        },
        mounted() {


        },

        created() {
            const options = this.question.type.question_type_answers.map(q => q.options);
            this.checkBoxOptions.push(...options);

            const multiSelect = options.flatMap(q => q.multi_select_options);
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