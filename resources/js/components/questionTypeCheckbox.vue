<template>

    <div class="custom-checkbox">
        <div class="row">
            <div v-for="(checkBox, index) in checkBoxValues">
                <label v-show="checkBox.value !== null">
                    <input class="checkbox checkbox-info checkbox-circle"
                           type="checkbox"
                           v-model="checkBox.checked"> <span
                        style="padding-left:1%">{{checkBox.value}}</span>
                </label> <br>

                <div v-if="hasCustomInputAndIsChecked(checkBox) || hasCustomInputSingleCase(checkBox)">
                    <input class="text-field"
                           :type="getCustomInputType(checkBox)"
                           v-model="checkBox.customInput"
                           :disabled="answerChecked && isSingleCustomInput"
                           :placeholder="getCustomInputPlaceholder(checkBox)">
                </div>
            </div>

        </div>
        <!--next button-->
        <mdbBtn v-show="isActive"
                color="primary"
                class="next-btn"
                :disabled="!answerChecked"
                @click="handleAnswers">
            {{isLastQuestion ? 'Complete' : 'Next'}}
            <font-awesome-icon v-show="waiting" icon="spinner" :spin="true"/>
        </mdbBtn>
    </div>

</template>

<script>

    import CheckboxCustomTypeCancer from "./checkboxCustomTypeCancer";
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
                cancerInputData: [],
                eyeProblemsInputData: [],
                stdProblemsInputData: [],
                cancerTypeAnswer: [],
                eyeProblemsTypeAnswer: [],
                stdProblemsTypeAnswer: [],
                sex: false,
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

            answerChecked() {
                return this.checkBoxValues.filter(q => q.checked === true).length > 0
                    || this.checkBoxValues.filter(q => q.customInput.length > 0).length > 0;
            },

            disableCheckBox() {
                return this.checkBoxValues.filter(q => q.customInput.length > 0).length > 0;
            },

            isSingleCustomInput() {
                return this.checkBoxValues.filter(q => q.options && !!q.options.allow_single_custom_input).length > 0;
            },

        },

        methods: {
            hasCustomInputSingleCase(checkBox) {
                return checkBox.options && !!checkBox.options.allow_single_custom_input;
            },

            getCustomInputType(checkBox) {
                if (checkBox.options !== null) {
                    return checkBox.options.answer_type
                }
                return "text";
            },

            getCustomInputPlaceholder(checkBox) {
                if (checkBox.options !== null) {
                    return checkBox.options.placeholder
                }
                return '';
            },

            hasCustomInputAndIsChecked(checkBox) {
                return checkBox.options && !!checkBox.options.allow_custom_input && checkBox.checked;
            },


            /* handleClick(answerValue) {
                 this.showNextButton = true;

             },*/

            handleAnswers() {
                const answer = [];
                const checkedCheckBoxes = this.checkBoxValues.filter(q => q.checked === true);
                checkedCheckBoxes.forEach(checkBox => {
                    const obj = {};
                    if (checkBox.options === null) { //there are cases where checkBox doesnt have options.
                        answer.push({name: checkBox.value})
                    } else if (checkBox.customInput !== null) {
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
        width: 420px;
        height: 55px;
        border-radius: 5px;
        border: solid 1px #f2f2f2;
        background-color: #ffffff;
        padding-top: 3%;
        padding-left: 5%;
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