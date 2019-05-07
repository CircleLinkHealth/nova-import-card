<template>
    <div class="row">
        <div class="custom-checkbox">
            <div v-for="(checkBox, index) in checkBoxValues">
                <label>
                    <input class="checkbox checkbox-info checkbox-circle"
                           type="checkbox"
                           v-model="checkBox.checked"> <span style="padding-left:1%">{{checkBox.value}}</span>
                </label>

                <div v-if="hasCustomInput(checkBox) && checkBox.checked">
                    <input class="text-field"
                           type="text"
                           v-model="checkBox.customInput">
                    <!--:placeholder="inputData.placeholder"-->
                </div>
            </div>

            <!--next button-->
            <mdbBtn v-show="isActive"
                    color="primary"
                    class="next-btn"
                    :disabled="!checkBoxChecked"
                    @click="handleAnswers">
                {{isLastQuestion ? 'Complete' : 'Next'}}
                <font-awesome-icon v-show="waiting" icon="spinner" :spin="true"/>
            </mdbBtn>
        </div>
    </div>
</template>

<script>

    import CheckboxCustomTypeCancer from "./checkboxCustomTypeCancer";
    import {EventBus} from "../event-bus";
    import CheckboxCustomTypeEyeProblems from "./checkboxCustomTypeEyeProblems";
    import CheckboxCustomTypeStd from "./checkboxCustomTypeStd";
    import {mdbBtn} from "mdbvue";
    import {library} from '@fortawesome/fontawesome-svg-core';
    import {faSpinner} from '@fortawesome/free-solid-svg-icons';
    import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

    library.add(faSpinner);
    export default {
        name: "questionTypeCheckbox",
        props: ['question', 'userId', 'surveyInstanceId', 'isActive', 'isSubQuestion', 'onDoneFunc', 'isLastQuestion', 'waiting'],
        components: {
            CheckboxCustomTypeStd,
            CheckboxCustomTypeEyeProblems,
            CheckboxCustomTypeCancer,
            mdbBtn,
            FontAwesomeIcon
        },

        data() {
            return {
                checkBoxValues: [],
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
                if (this.checkBoxValues.length > 0 && this.hasAnswerType) {
                    return this.checkBoxValues[0].id;
                } else {
                    return 0;
                }
            },

            checkBoxChecked() {
                return this.checkBoxValues.filter(q => q.checked === true).length > 0;
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


        },

        methods: {

            hasCustomInput(checkBox) {
                return checkBox.options && !!checkBox.options.allow_custom_input;
            },

            handleClick(answerValue) {
                this.showNextButton = true;

            },

            handleAnswers() {//@todo: also save text answers types
                /*        const answer = [];
                        for (let j = 0; j < this.checkedAnswers.length; j++) {
                            const val = this.checkedAnswers[j];
                            const q = this.checkBoxValues.find(x => x.value === val);
                            if (!this.questionOptions) {
                                answer.push({[q.options.key]: val});
                            } else {
                                answer.push({name: val})
                            }

                        }
                        var answerData = JSON.stringify(answer);*/

                const checkedCheckBoxes = this.checkBoxValues.filter(q => q.checked === true);
                const answer = [];
                checkedCheckBoxes.forEach(checkBox => {
                    const obj = {};
                    if (checkBox.customInput !== null) {
                        const obj = {
                            [checkBox.options.key]: checkBox.value,
                            type: checkBox.customInput
                        };

                        answer.push(obj);
                    } else {
                        obj[checkBox.options.key] = checkBox.value;
                        answer.push(obj);
                    }
                });
                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer);
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

            this.checkBoxValues = this.question.type.question_type_answers.map(x => {
                return Object.assign({}, x, {checked: false, customInput: ''});
            });

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

    .btn-primary {
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .btn-primary.disabled {
        opacity: 50%;
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

</style>