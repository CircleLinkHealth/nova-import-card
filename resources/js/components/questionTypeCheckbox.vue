<template>
    <div class="custom-checkbox">
        <div v-for="(checkBox, index) in checkBoxValues" :key="index">
            <label>{{checkBox.value}}
                <input class="check-box"
                       type="checkbox"
                       name="checkboxTypeAnswer[]"
                       :value="checkBox.value"
                       :disabled="!isActive"
                       v-model="checkedAnswers">
            </label>
            <!--   <div>
                   <input id="different-input"
                          class="text-field"
                          name="textTypeAnswer"
                          v-model="customInputHasText[index]"
                          :placeholder="checkBox.options.placeholder"
                          :type="checkBox.options.placeholder">
               </div>-->
        </div>
        <!--next button-->

        <mdbBtn v-show="isActive"
                color="primary"
                class="next-btn"
                :disabled="checkedAnswers.length === 0"
                @click="handleAnswers">
            {{isLastQuestion ? 'Complete' : 'Next'}}
            <font-awesome-icon v-show="waiting" icon="spinner" :spin="true"/>
        </mdbBtn>

    </div>
</template>

<script>

    import {mdbBtn} from "mdbvue";

    export default {
        name: "questionTypeCheckbox",
        props: ['question', 'userId', 'surveyInstanceId', 'isActive', 'isSubQuestion', 'onDoneFunc', 'isLastQuestion', 'waiting'],
        components: {
            'mdb-btn': mdbBtn
        },

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers,
                checkedAnswers: [],
                questionOptions: [],
                keyForValues: {},
                showDifferentInput: false,
                customInputHasText: []
            }
        },
        computed: {

            hasAnswerType() {
                return this.checkBoxValues.length !== 0;
            },

            questionTypeAnswerId() {
                if (this.hasAnswerType) {
                    return this.checkBoxValues[0].id;
                } else {
                    return 0;
                }
            },
            /*//:todo:get which checkboses have diff typr input and set this.showDifferentInput === 0 ()*/
            checkBoxesWithDifferentInputType() {
                //get which checkboxe have allow custom input
                const hasAllowCustomInput = this.checkBoxValues.filter(checkBox => checkBox.options.hasOwnProperty('allow_custom_input'));
                //check through checked answers OR last checked answer
                //return hasAllowCustomInput.map(q => q);

            },
        },

        methods: {

            handleAnswers() {
                const answer = [];
                for (let j = 0; j < this.checkedAnswers.length; j++) {
                    const val = this.checkedAnswers[j];
                    const q = this.checkBoxValues.find(x => x.value === val);
                    answer.push({[q.options.key]: val});
                }

                this.onDoneFunc(this.question.id, this.questionTypeAnswerId, answer).then(() => {});
            }
        },

        created() {
            const options = this.checkBoxValues.map(checkBoxValue => checkBoxValue.options);
            this.questionOptions.push(...options);
        },
    }
</script>

<style scoped>
</style>
