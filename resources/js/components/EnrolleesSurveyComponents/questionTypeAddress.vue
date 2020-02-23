<template>
    <div class="scroll-container">
        <div class="col-md-12 active">
            <input type="text"
                   class="text-field margin-bottom-10"
                   v-model="inputHasText"
                   :disabled="readOnly"/>
        </div>

        <br>
        <!--next button-->
        <div :class="isLastQuestion ? 'text-center' : 'text-left'">
            <mdbBtn v-show="!readOnly && isActive"
                    color="primary"
                    class="next-btn"
                    name="number"
                    id="number"
                    :disabled="!(isOptional || inputHasValue)"
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
                inputHasText: '',
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
                    value: this.inputHasText
                };

                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer, this.isLastQuestion);
            },
        },
        computed: {
            inputHasValue() {
                const input = this.inputHasText;
                return input.length !== 0;
            }
        },


        created() {
            if (this.question.identifier === 'Q_CONFIRM_EMAIL') {
                this.inputHasText = this.nonAwvPatients.patientEmail;
            }

            if (this.question.identifier === 'Q_CONFIRM_ADDRESS') {
                this.inputHasText = this.nonAwvPatients.address;
            }
        }
    }
</script>

<style scoped>

</style>
