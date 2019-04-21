<template>
    <div>
        <div class="row">
            <div class="checkbox-dropdown col-lg-4" v-for="answer in previousQuestionAnswers">
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
        props: ['question', 'userId', 'surveyInstanceId'],
        components: {},

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers[0].value,
                checkBoxOptions: [],
                multiSelectOptions: [],
                previousQuestionAnswers: [
                    {
                        name: 'Colorectal Cancer'
                    },
                    {
                        name: 'Depression'
                    }
                ],
            }
        },
        computed: {
            placeHolder() {
                return this.checkBoxOptions[0].placeholder
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