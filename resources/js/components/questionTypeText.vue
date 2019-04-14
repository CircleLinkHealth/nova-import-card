<template>
    <div>
        <!--question without sub_parts-->
        <div v-if="!questionHasSubParts">
            <input
                    type="text"
                    class="text-field"
                    name="textTypeAnswer[]"
                    v-model="inputHasText"
                    :placeholder="this.questionPlaceHolder"
                    @change="onInput">
        </div>

        <br>
        <!--question with sub_parts-->
        <div class="row">
            <div v-if="questionHasSubParts"
                 v-for="(subPart, index) in subParts" :key="index" style="margin-left: 15%;">
                <label class="label" v-if="questionHasSubParts">{{subPart.title}}</label><br>
                <input type="text"
                       class="text-field"
                       name="textTypeAnswer[]"
                       v-model="inputHasText[index]"
                       :placeholder="subPart.placeholder"
                       @change="onInput">

                <!--add input fields button-->
                <!--@todo:extraFieldButton should be out of loop-->

                <div v-for="extraFieldButtonName in extraFieldButtonNames">
                    <div v-if="canAddInputFields">
                        <button type="button"
                                @click="addInputFields(subPart.title, subPart.placeholder, subPart.key)"
                                class="btn-add-field">
                            {{extraFieldButtonName.add_extra_answer_text}}
                        </button>
                    </div>

                    <div v-if="canRemoveInputFields">
                        <button type="button"
                                @click="removeInputFields(index)"
                                class="btn-primary">
                            {{extraFieldButtonName.remove_extra_answer_text}}
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!--next button-->
        <div v-if="hasTypedTwoNumbers">
            <button class="next-btn"
                    name="text"
                    id="text"
                    type="submit"
                    @click="handleAnswer()">Next
            </button>
        </div>
    </div>
</template>

<script>

    import {EventBus} from "../event-bus";

    export default {
        name: "questionTypeText",
        props: ['question', 'userId', 'surveyInstanceId'],

        mounted() {

        },

        data() {
            return {
                inputHasText: [],
                questionOptions: [],
                subParts: [],
                extraFieldButtonNames: [],
                canRemoveInputFields: false,
                canAddInputFields: false,
                showNextButton: false,
            }
        },
        computed: {
            hasTypedTwoNumbers() {
                return this.inputHasText.length > 1 ? this.showNextButton = true : this.showNextButton = false;
            },

            hasAnswerType() {
                return this.question.type.question_type_answers.length !== 0;
            },

            questionTypeAnswerId() {
                if (this.hasAnswerType) {
                    return this.question.type.question_type_answers[0].id;
                } else {
                    return 0;
                }
            },

            questionHasSubParts() {
                if (this.hasAnswerType) {
                    return this.questionOptions[0].hasOwnProperty('sub_parts');
                }
                return false;
            },

            questionHasPlaceHolderInSubParts() {
                if (this.questionHasSubParts) {
                    const placeholder = this.questionOptions[0].sub_parts.map(q => q.placeholder);
                    if (placeholder) {
                        return true;
                    }
                }
                return false;
            },

            questionHasPlaceHolderInOptions() {
                if (!this.questionHasPlaceHolderInSubParts && this.questionOptions.length !== 0) {
                    const placeholder = this.questionOptions[0].placeholder;
                    if (placeholder) {
                        return true;
                    }
                }
                return false;
            },

            questionPlaceHolder() {
                if (this.questionHasPlaceHolderInSubParts && !this.questionHasPlaceHolderInOptions) {
                    return this.questionOptions[0].sub_parts.map(q => q.placeholder);
                }
                if (this.questionHasPlaceHolderInOptions) {
                    return this.questionOptions[0].placeholder;
                }
                return '';
            },

            addInputFieldsButtonName() {
                if (this.hasAnswerType) {
                    return this.questionOptions[0].add_extra_answer_text;
                }
                return '';

            },

            removeInputFieldsButtonName() {
                if (this.hasAnswerType) {
                    return this.questionOptions[0].remove_extra_answer_text;
                }
                return '';
            },

        },

        methods: {
            onInput() {
                EventBus.$emit('handleTextType');
            },

            addInputFields(title, placeholder, key) {
                /*  const label = this.subParts.map(q => q.title);
                  const placeholder = this.subParts.map(q => q.placeholder);
                  const key = this.subParts.map(q => q.key);*/

                this.subParts.push({
                    title: title,
                    placeholder: placeholder,
                    key: key
                });

                this.canRemoveInputFields = true;

            },
            /*@todo:delete answer also*/
            removeInputFields(index) {
                // this.delete(this.subParts, index);
                this.subParts.splice(index, 1);
            },

            handleAnswer() {
                const answer = [];
                if (this.subParts.length === 0) {
                    const key = 'value';
                    answer.push({[key]: this.inputHasText})
                } else {
                    for (let j = 0; j < this.inputHasText.length; j++) {
                        const val = this.inputHasText[j];
                        const q = this.subParts[j];
                        if (q) {
                            answer.push({[q.key]: val});
                        }
                    }
                }
                var answerData = JSON.stringify(answer);

                axios.post('/save-answer', {
                    user_id: this.userId,
                    survey_instance_id: this.surveyInstanceId[0],
                    question_id: this.question.id,
                    question_type_answer_id: this.questionTypeAnswerId,
                    value: answerData,
                })
                    .then(function (response) {
                        console.log(response);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        },

        created() {
            const questionOptions = this.question.type.question_type_answers.map(q => q.options);
            this.questionOptions.push(...questionOptions);

            if (this.hasAnswerType) {
                const buttonNames = questionOptions.map(q => q);
                this.extraFieldButtonNames.push(...buttonNames);
            }

            /*sets subQuestions data*/
            if (this.questionHasSubParts) {
                const subQuestions = this.questionOptions[0].sub_parts;
                this.subParts.push(...subQuestions);
            }
            /*sets canAddInputField data*/
            if (this.hasAnswerType) {
                return this.questionOptions[0].allow_multiple === true ? this.canAddInputFields = true : '';
            }
            return false;
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

    .btn-add-field {
        width: 271px;
        height: 40px;
        font-family: Poppins;
        font-size: 24px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.3px;
        color: #50b2e2;

    }

    .text-field {
        border: none;
        border-bottom: solid 1px rgba(0, 0, 0, 0.1);
        background-color: transparent;
        outline: 0;
        width: 300px;
        height: 30px;
    }

    .label {
        width: 64px;
        height: 40px;
        font-family: Poppins;
        font-size: 20px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.3px;
        color: #1a1a1a;
    }
</style>