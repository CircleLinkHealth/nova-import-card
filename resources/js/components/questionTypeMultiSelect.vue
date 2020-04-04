<template>
    <div class="scroll-container">
        <div class="row no-gutters scrollable">
            <div class="col-md-6 no-gutters select-container"
                 @click="onSelectClick(index)"
                 v-for="(select, index) in selectBoxes">
                <div class="col-md-12 select-title" :class="{active: select.active}">
                    {{select.key}}
                </div>
                <div class="col-md-12 select-dropdown" :class="{active: select.active}" @click="positionOnTop">
                    <vue-select multiple @input="function (val) {onOptionSelected(select, val)}"
                                :value="select.selected"
                                :close-on-select="false"
                                :options="select.options"
                                :searchable="false"
                                :disabled="readOnly"
                                :placeholder="select.placeholder"/>
                </div>
            </div>
        </div>

        <br/>

        <mdbBtn v-show="!readOnly && isActive"
                color="primary"
                class="next-btn"
                :disabled="!hasSelections"
                @click="handleAnswer">
            {{isLastQuestion ? 'Complete' : 'Next'}}
            <mdb-icon v-show="waiting" icon="spinner" :spin="true"/>
        </mdbBtn>

    </div>

</template>

<script>

    import {mdbBtn, mdbIcon} from 'mdbvue';
    import vueSelect from 'vue-select';

    export default {
        name: "questionTypeMultiSelect",
        props: ['question', 'isActive', 'isSubQuestion', 'onDoneFunc', 'isLastQuestion', 'waiting', 'getAllQuestionsFunc', 'readOnly'],
        components: {mdbIcon, vueSelect, mdbBtn},

        data() {
            return {
                options: null,
                selectBoxes: [],
            }
        },

        computed: {

            hasSelections() {
                return this.selectBoxes.every(s => s.selected.length > 0);
            }

        },

        watch: {
            isActive: function (newVal, oldVal) {
                if (newVal) {
                    this.refreshSelections();
                }
            }
        },

        methods: {

            onOptionSelected(select, value) {
                select.selected = value;
            },

            onSelectClick(index) {
                this.selectBoxes.forEach((s, i) => {
                    s.active = index === i;
                });
            },

            handleAnswer() {
                const key = this.options[0].key;
                const selectKey = this.options[0].multi_select_key;
                let questionTypeAnswerId = 0;
                if (this.question.type && this.question.type.question_type_answers && this.question.type.question_type_answers.length) {
                    questionTypeAnswerId = this.question.type.question_type_answers[0].id;
                }
                const answer = [];
                this.selectBoxes.forEach(s => {
                    const result = {};
                    result[key] = s.key;
                    result[selectKey] = s.selected;
                    answer.push(result);
                });
                this.onDoneFunc(this.question.id, questionTypeAnswerId, answer, this.isLastQuestion);
            },

            refreshSelections() {
                this.options = this.question.type.question_type_answers.map(q => q.options);

                const shouldImportCheckboxValuesFromOtherAnswer = this.options.length && this.options[0].import_answers_from_question;
                if (shouldImportCheckboxValuesFromOtherAnswer) {
                    const questionOrder = this.options[0].import_answers_from_question.question_order;
                    const questions = this.getAllQuestionsFunc();
                    const targetQuestion = questions.find(q => q.pivot.order === questionOrder);
                    //should never happen
                    if (!targetQuestion.answer) {
                        targetQuestion.answer = {value: []};
                    }

                    const selectOptions = this.options[0].multi_select_options;
                    const placeholder = this.options[0].placeholder;

                    const key = this.options[0].key;
                    const selectKey = this.options[0].multi_select_key;

                    this.selectBoxes = targetQuestion.answer.value.map(v => {
                        let selected = [];

                        let valueFromServer;
                        if (this.question.answer && this.question.answer.value) {
                            valueFromServer = this.question.answer.value.find(x => x[key] === v[key]);
                        } else if (this.question.answer && this.question.answer.suggested_value) {
                            valueFromServer = this.question.answer.suggested_value.find(x => x[key] === v[key]);
                        }

                        if (valueFromServer) {
                            selected = valueFromServer[selectKey];
                        }
                        return {key: v[key], options: selectOptions, placeholder, active: false, selected};
                    });

                } else {
                    //todo
                }
            },

            positionOnTop(e) {
                const elem = $(e.currentTarget);
                const offset = elem.offset();
                const top = offset.top + elem.height();
                const left = offset.left;
                const width = elem.width();

                const target = $('ul[role="listbox"]');
                target.css('position', 'fixed');
                target.css('top', `${top}px`);
                target.css('left', `${left}px`);
                target.css('width', `${width}px`);
            }

        },
        mounted() {
            this.refreshSelections();
        },

        created() {
        },
    }
</script>

<style scoped>

    .select-container {
        margin-bottom: 30px;
    }

    .select-container:nth-child(odd) {
        padding-right: 20px;
    }

    .select-container:nth-child(even) {
        padding-left: 20px;
    }

    .select-title {
        font-family: Poppins, serif;
        font-size: 24px;
        font-weight: 500;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: 1.33px;
        color: #d5dadd;
        margin-bottom: 20px;
    }

    .select-title.active {
        color: #1a1a1a;
    }

    @media (max-width: 490px) {
        .select-title {
            font-size: 15px;
        }

        .select-dropdown{
            font-size: 15px;
        }
        .select-container:nth-child(odd) {
            padding-right: unset;
        }

        }
</style>
