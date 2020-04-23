<template>
    <div class="scroll-container">
        <div class="row no-gutters scrollable">
            <div>
                <a style="color: #50b2e2; text-underline: #50b2e2"
                   @click="openInNewWindow()">
                    Click here to review letter
                </a>
            </div>

                <br>

            <label>
                <input class="checkbox checkbox-info checkbox-circle"
                       type="checkbox"
                       v-model="checked">
                <span>I confirm i have read the letter</span>
            </label>
            <br/>
        </div>

        <br/>

        <mdbBtn v-show="!readOnly && isActive"
                color="primary"
                class="next-btn"
                :disabled="!checked"
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
        props: [
            'question',
            'enrollmentSurveyPatients',
            'isActive',
            'onDoneFunc',
            'isLastQuestion',
            'waiting',
            'readOnly',
            'userId'
        ],
        components: {mdbBtn, mdbIcon},

        data() {
            return {
                checked: false,
                letterLink: ''
            }
        },

        computed: {},
        methods: {
            handleAnswers() {
                const answer = this.checked;
                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer, this.isLastQuestion);
            },

            openInNewWindow() {
                window.open(this.letterLink, "_blank")
            }
        },

        created() {
            this.letterLink = this.enrollmentSurveyPatients.letterLink;
        }
    }


</script>


<style scoped>

    .scrollable label {
        width: 100%;
        height: 55px;
        border-radius: 5px;
        border: solid 1px #f2f2f2;
        background-color: #ffffff;
        padding-top: 15px;
        padding-left: 10px;
        cursor: pointer;
    }

    .scrollable label:hover {
        border-color: #4aa5d2;
    }

    .scrollable label > span {
        /*padding-left: 3px;*/
    }

    /*** custom checkboxes ***/
    .scrollable input[type=checkbox] {
        position: absolute;
        left: -999px;
    }

    /* to hide the checkbox itself */
    .scrollable label input[type=checkbox] + span:before {
        background-color: #FFFFFF;
        color: #50b2e2;
        font-family: "Font Awesome 5 Free", serif;
        display: inline-block;
        content: "\f111";
        letter-spacing: 5px;
        position: relative;
        font-size: 1.3em;
        top: 2px;
    }

    /* space between checkbox and label */
    .scrollable label input[type=checkbox]:checked + span:before {
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
        width: 100%;
        height: 30px;
    }

    .text-field:active, .text-field:focus {
        border-color: #4aa5d2;
    }

    @media (max-width: 490px) {
        .scrollable label {
            height: fit-content;
            padding: 8px;
            font-size: 13px;
            font-weight: 400;
        }

        .scrollable label input[type=checkbox] + span:before {
            font-size: 1em;
        }

        .text-field {
            font-size: 15px;
        }

    }

</style>

