<template>
    <div class="scroll-container">
        <div class="row no-gutters scrollable">

            <input class="checkbox checkbox-info checkbox-circle"
                   type="checkbox">
            <span>jhgjkkgkgkgkhj</span>


            <br/>

        </div>

        <br/>

        <mdbBtn v-show="!readOnly && isActive"
                color="primary"
                class="next-btn"
                :disabled="!answerChecked"
                @click="handleAnswers">
            {{isLastQuestion ? 'Complete' : 'Next'}}
            <mdb-icon v-show="waiting" icon="spinner" :spin="true"/>
        </mdbBtn>
    </div>
</template>

<script>
    import {mdbBtn, mdbIcon} from "mdbvue";

    export default {
        name: "questionTypeConfirmation",
        props: ['question', 'nonAwvPatients', 'isActive', 'onDoneFunc', 'isLastQuestion', 'waiting', 'readOnly'],
        components: {mdbBtn, mdbIcon},

        data() {
            return {}
        },

        computed: {
            answerChecked() {
                if (this.question.optional) {
                    return true;
                }
            },

            methods: {
                handleAnswers() {
                    this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer, this.isLastQuestion);
                },
            },
        }
    }

</script>

<style scoped>

</style>
