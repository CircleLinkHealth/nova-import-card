<template>
    <div class="scroll-container">
        <div v-if="!isMultiInput" class="col-md-12 active">
            <input type="text"
                   class="address-field"
                   v-model="singleInputHasText"
                   :disabled="readOnly"/>
        </div>
        <div v-else>
            <div v-for="(item, index) in physicalAddress[0]"
                 class="col-md-12 active">
                <br>
                <label class="label">{{index}}</label>
                <br>
                <input type="text"
                       class="address-field"
                       v-model="item"
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
                {{isLastQuestion ? 'Complete' : 'Next'}}
                <mdb-icon v-show="waiting" icon="spinner" :spin="true"/>
            </mdbBtn>
        </div>
    </div>
</template>

<script>
    import {mdbBtn, mdbIcon} from "mdbvue";

    export default {
        name: "questionTypeAddress",
        props: ['question', 'nonAwvPatients', 'isActive', 'onDoneFunc', 'isLastQuestion', 'waiting', 'readOnly'],
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
            handleAnswer() {
                const answer = {
                    value: this.singleInputHasText
                };

                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer, this.isLastQuestion);
            },
        },
        computed: {
            singeInputHasValue() {
                const input = this.singleInputHasText;
                return input.length !== 0;
            },
            hasTypedInAllInputs() {
                return Object.keys(this.physicalAddress).every(key => key.length > 0);
            },
        },


        created() {
            if (this.question.identifier === 'Q_CONFIRM_EMAIL') {
                this.singleInputHasText = this.nonAwvPatients.patientEmail;
            }

            if (this.question.identifier === 'Q_CONFIRM_ADDRESS') {
                this.isMultiInput = true;
                const x = Object.assign({}, {
                    address: this.nonAwvPatients.address,
                    city: this.nonAwvPatients.city,
                    state: this.nonAwvPatients.state,
                    zip: this.nonAwvPatients.zip,
                });
                this.physicalAddress.push(x);
                // this.physicalAddress.address = this.nonAwvPatients.address;
                // this.physicalAddress.city = this.nonAwvPatients.city;
                // this.physicalAddress.state = this.nonAwvPatients.state;
                // this.physicalAddress.zip = this.nonAwvPatients.zip;
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
