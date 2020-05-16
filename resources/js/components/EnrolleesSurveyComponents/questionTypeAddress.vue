<template>
    <div class="scroll-container">
        <div v-if="!isMultiInput" class="col-md-12 active">
            <input type="text"
                   class="address-field"
                   :placeholder="placeholderValue"
                   v-model="singleInputHasText"
                   :disabled="readOnly"/>
        </div>
        <div v-else>
            <div v-for="(item, index) in physicalAddress"
                 class="col-md-12 active">
                <br>
                <label class="label">{{capitalizeFirstLetter(index)}}</label>
                <br>
                <!-- Dont really understand why i should do this in v-model to work  -->
                <input type="text"
                       class="address-field"
                       v-model="physicalAddress[index]"
                       :disabled="readOnly"/>

            </div>
        </div>
        <br>
        <!--next button-->
        <div :class="isLastQuestion ? 'text-center' : 'text-left'">
            <mdbBtn v-show="!readOnly && isActive"
                    color="primary"
                    class="next-btn"
                    name="number"
                    id="number"
                    :disabled="!(isOptional || singeInputHasValue || hasTypedInAllInputs)"
                    @click="handleAnswer()">
                {{buttonText}}
                <mdb-icon v-show="waiting" icon="spinner" :spin="true"/>
            </mdbBtn>
        </div>
    </div>
</template>

<script>
    import {mdbBtn, mdbIcon} from "mdbvue";

    export default {
        name: "questionTypeAddress",
        props: ['question', 'enrollmentSurveyPatients', 'isActive', 'onDoneFunc', 'isLastQuestion', 'waiting', 'readOnly'],
        components: {mdbBtn, mdbIcon},

        data() {
            return {
                isMultiInput: false,
                singleInputHasText: '',
                physicalAddress: [],
                questionOptions: [],
                subParts: [],
                showNextButton: false,
                singleTitle: undefined,
                placeholderForSingleQuestion: [],
                isOptional: false
            }
        },
        methods: {

            capitalizeFirstLetter(text) {
                if (!text) {
                    return text;
                }
                return text[0].toUpperCase() + text.slice(1);
            },

            handleAnswer() {
                let answer = [];
                if (this.isMultiInput) {
                    Object.entries(this.physicalAddress).forEach(p => {
                        const values = {};
                        values[p[0]] = p[1];
                        answer.push(values);
                    });
                } else {
                    let value = this.singleInputHasText;
                    if(this.singleInputHasText === ''
                        && this.enrollmentSurveyPatients.patientEmail.email !== ''){
                            value = this.enrollmentSurveyPatients.patientEmail.email;
                    }

                    answer = {
                        value: value
                    }
                }
                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer, this.isLastQuestion);
            },
        },
        computed: {
            placeholderValue() {
                return this.singleInputHasText !== '' ? '' : 'Enter you email'
            },

            buttonText() {
                if (this.isLastQuestion) {
                    return 'Complete';
                }
                if (this.singleInputHasText === '' && !this.isMultiInput) {
                    return "I don't have an email";
                }
                return 'Next';
            },

            singeInputHasValue() {
                const input = this.singleInputHasText;
                return input !== undefined ? input.length !== 0 : false;
            },
            hasTypedInAllInputs() {
                const data = Object.values(this.physicalAddress);
                return data.every(key => key !== undefined ? key.length > 0 : false);
            },
        },


        created() {
            if (this.question.identifier === 'Q_CONFIRM_EMAIL') {
                this.singleInputHasText = this.enrollmentSurveyPatients.patientEmail.viewEmail;
            }

            if (this.question.identifier === 'Q_CONFIRM_ADDRESS') {
                this.isMultiInput = true;
                this.physicalAddress = {
                    address: this.enrollmentSurveyPatients.address,
                    city: this.enrollmentSurveyPatients.city,
                    state: this.enrollmentSurveyPatients.state,
                    zip: this.enrollmentSurveyPatients.zip,
                };


            }
        }
    }
</script>

<style scoped>
    .btn-primary {
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .btn-primary.disabled {
        opacity: 50%;
        background-color: #50b2e2;
        border-color: #4aa5d2;
    }

    .address-field {
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
        width: 300px;
        height: 30px;
    }


    @media (max-width: 490px) {
        .address-field {
            padding-left: 9px;
            width: 325px;
        }
    }
</style>
