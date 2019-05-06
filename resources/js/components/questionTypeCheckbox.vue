<template>
    <div class="row">
        <div class="custom-checkbox">
            <div v-for="(checkBox, index) in checkBoxValues">
                <label>
                    <input class="checkbox checkbox-info checkbox-circle"
                           type="checkbox"
                           name="checkboxTypeAnswer"
                           :value="checkBox.value"
                           v-model="checkedAnswers"
                           @click="handleClick(checkBox.value)"> <span style="padding-left:1%">{{checkBox.value}}</span>
                </label>

            </div>

            <checkbox-custom-type-cancer
                    :cancerInputData="cancerInputData"
                    v-if="cancerCustomInput">
            </checkbox-custom-type-cancer>

            <checkbox-custom-type-eye-problems
                    :eyeProblemsInputData="eyeProblemsInputData"
                    v-if="eyeProblemsCustomInput">
            </checkbox-custom-type-eye-problems>

            <checkbox-custom-type-std
                    :stdProblemsInputData="stdProblemsInputData"
                    v-if="stdCustomInput">
            </checkbox-custom-type-std>
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
    </div>
</template>

<script>

    import CheckboxCustomTypeCancer from "./checkboxCustomTypeCancer";
    import {EventBus} from "../event-bus";
    import CheckboxCustomTypeEyeProblems from "./checkboxCustomTypeEyeProblems";
    import CheckboxCustomTypeStd from "./checkboxCustomTypeStd";

    export default {
        name: "questionTypeCheckbox",
        props: ['question','onDoneFunc'],
        components: {
            CheckboxCustomTypeStd,
            CheckboxCustomTypeEyeProblems,
            'checkbox-custom-type-cancer': CheckboxCustomTypeCancer
        },

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers,
                showNextButton: false,
                checkedAnswers: [],
                questionOptions: [],
                showDifferentInput: false,
                questionsWithCustomInput: [],
                customInputHasText: [],
                cancerInputData: [],
                eyeProblemsInputData: [],
                stdProblemsInputData: [],
                cancerTypeAnswer: [],
                eyeProblemsTypeAnswer: [],
                stdProblemsTypeAnswer: [],
            }
        },
        computed: {

            hasAnswerType() {
                return this.checkBoxValues.length !== 0;
            },

            questionTypeAnswerId() {
                if (this.hasAnswerType) {
                    return this.checkBoxValues[0].id;
                } else {
                    return 0;
                }
            },

            /* checkCustomInputs() {
                 //@todo:unfinished-issues:need to check foreach key if value = true.then check each value and act
                 //or maybe just dont...
                 const questionsCustomValue = this.questionsWithCustomInput.map(q => q.value);
                 var check = [];
                 for (let j = 0; j < questionsCustomValue.length; j++) {
                     const val = questionsCustomValue[j];
                     const q = this.checkedAnswers.includes(val);
                     check.push({[val]: q});
                 }
                 return check;
             },*/

            cancerCustomInput() {
                return this.checkedAnswers.includes('Cancer');
            },
            stdCustomInput() {
                return this.checkedAnswers.includes('Sexually Transmitted Disease/Infection');
            },
            eyeProblemsCustomInput() {
                return this.checkedAnswers.includes('Eye Problems');
            },

        },

        methods: {
            handleClick(answerValue) {
                this.showNextButton = true;

            },

            handleAnswers() {//@todo: also save text answers types
                const answer = [];
                for (let j = 0; j < this.checkedAnswers.length; j++) {
                    const val = this.checkedAnswers[j];
                    const q = this.checkBoxValues.find(x => x.value === val);
                    if (!this.questionOptions) {
                        answer.push({[q.options.key]: val});
                    } else {
                        answer.push({name: val})
                    }

                }
                var answerData = JSON.stringify(answer);
                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answerData);
            },


        },

        mounted() {
            EventBus.$on('cancerInputValue', (answerVal) => {
                this.cancerTypeAnswer.push(answerVal);
            });

            EventBus.$on('eyesProblemInputValue', (answerVal) => {
                this.eyeProblemsTypeAnswer.push(answerVal);
            });

            EventBus.$on('sdtProblemInputValue', (answerVal) => {
                this.stdProblemsTypeAnswer.push(answerVal);
            });
        },

        created() {
            const x = this.checkBoxValues.filter(checkBoxValue => checkBoxValue.options !== null)
                .filter(checkBox => checkBox.options.hasOwnProperty('allow_custom_input'));
            this.questionsWithCustomInput.push(...x);

            const options = this.checkBoxValues.filter(checkBoxValue => checkBoxValue.options !== null).map(checkBoxValue => checkBoxValue.options);
            this.questionOptions.push(...options);

            const cancerTypeInputData = this.questionsWithCustomInput.filter(q => q.value === 'Cancer').map(q => q.options);
            this.cancerInputData.push(...cancerTypeInputData);

            const eyeProblemsInputData = this.questionsWithCustomInput.filter(q => q.value === 'Eye Problems').map(q => q.options);
            this.eyeProblemsInputData.push(...eyeProblemsInputData);

            const stdProblemsInputData = this.questionsWithCustomInput.filter(q => q.value === 'Sexually Transmitted Disease/Infection').map(q => q.options);
            this.stdProblemsInputData.push(...stdProblemsInputData);

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

    .custom-checkbox label {
        width: 450px;
        height: 50px;
        border-radius: 5px;
        border: solid 1px #f2f2f2;
        background-color: #ffffff;
        padding-top: 2%;
        padding-left: 7%;
    }


</style>