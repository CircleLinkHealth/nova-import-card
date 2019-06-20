<template>

    <div class="custom-checkbox">
        <div class="row no-gutters checkboxes">
            <div v-for="(checkBox, index) in checkBoxValues" class="col-md-6">
                <label v-show="checkBox.value !== null" :for="checkBox.id">
                    <input class="checkbox checkbox-info checkbox-circle"
                           type="checkbox"
                           :id="checkBox.id"
                           :name="checkBox.id"
                           v-model="checkBox.checked">
                    <span>{{checkBox.value}}</span>
                </label>

                <br/>

                <div v-if="hasCustomInputAndIsChecked(checkBox) || hasCustomInputSingleCase(checkBox)">
                    <input class="text-field"
                           :type="getCustomInputType(checkBox)"
                           v-model="checkBox.customInput"
                           :disabled="answerChecked && isSingleCustomInput"
                           :placeholder="getCustomInputPlaceholder(checkBox)">

                    <br/>
                    <br/>
                </div>
            </div>

        </div>

        <br/>

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
                    return undefined;
                }
            },

            answerChecked() {
                if (this.hasAnyCustomInputNotFilled()) {
                    return false;
                }
                else {
                    return this.checkBoxValues.filter(q => q.checked === true).length > 0;
                }
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

            hasAnyCustomInputNotFilled() {
                return this.checkBoxValues.some(c => {
                    return this.hasCustomInputAndIsChecked(c) && (c.customInput == null || c.customInput.length === 0);
                });
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

    .custom-checkbox {
        height: 100%;
    }

    .checkboxes {
        overflow-y: auto;
        height: 100%;
        max-height: calc(100% - 165px);
    }

    .checkboxes label {
        width: 100%;
        height: 55px;
        border-radius: 5px;
        border: solid 1px #f2f2f2;
        background-color: #ffffff;
        padding-top: 15px;
        padding-left: 10px;
    }

    .checkboxes label:hover {
        border-color: #4aa5d2;
    }

    .checkboxes label > span {
        /*padding-left: 3px;*/
    }

    /*** custom checkboxes ***/
    .checkboxes input[type=checkbox] {
        position: absolute;
        left: -999px;
    }

    /* to hide the checkbox itself */
    .checkboxes label input[type=checkbox] + span:before {
        background-color: #FFFFFF;
        color: #50b2e2;
        font-family: FontAwesome, serif;
        display: inline-block;
        content: "\f10c";
        letter-spacing: 5px;
        position: relative;
        font-size: 1.3em;
        top: 2px;
    }

    /* space between checkbox and label */
    .checkboxes label input[type=checkbox]:checked + span:before {
        content: "\f058";
    }

    /* allow space for check mark */

    .btn-primary {
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .btn-primary.disabled {
        opacity: 50%;
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .text-field {
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
        width: 300px;
        height: 30px;
    }

    .text-field:active, .text-field:focus {
        border-color: #4aa5d2;
    }

</style>
