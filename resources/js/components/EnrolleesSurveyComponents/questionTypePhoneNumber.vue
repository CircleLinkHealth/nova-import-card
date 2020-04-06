<template>
    <!-- @todo:   Phone input needs some tweaks.. Will do later-->
    <div class="scroll-container">
        <div class="scrollable">
            <div class="col-md-12 active">
                <VuePhoneNumberInput
                    no-country-selector
                    :only-coubtires="onlyCountries"
                    no-example
                    v-model="inputPhoneNumber"/>
            </div>

            <br>


            <!--next button-->
            <div :class="isLastQuestion ? 'text-center' : 'text-left'">
                <mdbBtn v-show="!readOnly && isActive"
                        color="primary"
                        class="next-btn"
                        name="number"
                        id="number"
                        :disabled="!(isOptional || hasTypedInNumber)"
                        @click="handleAnswer()">
                    {{isLastQuestion ? 'Complete' : 'Next'}}
                    <mdb-icon v-show="waiting" icon="spinner" :spin="true"/>
                </mdbBtn>
            </div>
        </div>
    </div>
</template>

<script>
    import {mdbBtn, mdbIcon} from "mdbvue";
    import VuePhoneNumberInput from 'vue-phone-number-input';
    import 'vue-phone-number-input/dist/vue-phone-number-input.css';

    export default {
        name: "questionTypePhoneNumber",
        props: ['question', 'enrollmentSurveyPatients', 'isActive', 'onDoneFunc', 'isLastQuestion', 'waiting', 'readOnly'],
        components: {mdbBtn, mdbIcon, VuePhoneNumberInput},

        data() {
            return {
                onlyCountries: ['US'],
                phoneValue: '',
                inputPhoneNumber: '',
                phoneNumberIsUsValid: false,
                preventNextIteration: false,
                showNextButton: false,
                singleTitle: undefined,
                placeholderForSingleQuestion: [],
                isOptional: false
            }
        },

        computed: {
            hasTypedInNumber() {
                return this.inputPhoneNumber.length > 0;
            },
        },

        methods: {
            handleAnswer() {
                const answer = this.phoneValue !== '' ? this.phoneValue : this.inputPhoneNumber;
                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer, this.isLastQuestion);
            },

        },

        created() {
            if (this.enrollmentSurveyPatients.preferredContactNumber.length > 0) {
                this.inputPhoneNumber = this.enrollmentSurveyPatients.preferredContactNumber[0];
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

    .phone-field {
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
        width: 300px;
        height: 30px;
    }


    @media (max-width: 490px) {
        .phone-field {
            padding-left: 9px;
            width: 325px;
        }
    }
</style>
