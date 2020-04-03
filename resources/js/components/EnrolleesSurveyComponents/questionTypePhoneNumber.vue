<template>
<!-- @todo:   Phone input needs some tweaks.. Will do later-->
    <div class="scroll-container">
        <div class="scrollable">
            <div class="col-md-12 active">
                <VuePhoneNumberInput
                    v-model="formattedPhoneValue"/>
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
        props: ['question', 'nonAwvPatients', 'isActive', 'onDoneFunc', 'isLastQuestion', 'waiting', 'readOnly'],
        components: {mdbBtn, mdbIcon, VuePhoneNumberInput},

        data() {
            return {
                phoneValue: '',
                formattedPhoneValue: '',
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
                return this.formattedPhoneValue.length === 12;
            },
        },

        methods: {
            handleAnswer() {
                const answer = this.phoneValue !== '' ? this.phoneValue : this.formattedPhoneValue;
                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer, this.isLastQuestion);
            },

            // checkNumber(event) {
            //     if (this.formattedPhoneValue !== ''
            //         && ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'].includes(event.key)) {
            //         this.phoneValue = this.formattedPhoneValue.replace(/-/g, '').match(/(\d{1,10})/g)[0];
            //         this.formattedPhoneValue = this.phoneValue.replace(/(\d{1,3})(\d{1,3})(\d{1,4})/g, '$1-$2-$3');
            //     } else {
            //         this.formattedPhoneValue = '';
            //     }
            // }
        },

        created() {
            if (this.nonAwvPatients.preferredContactNumber !== []) {
                this.formattedPhoneValue = this.nonAwvPatients.preferredContactNumber[0];
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
