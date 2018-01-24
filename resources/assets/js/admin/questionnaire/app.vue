<template>
    <div :class="className">
        <div class="form-group" v-for="(question, pIndex) in questions" :key="pIndex">
            <div class="question-text">{{pIndex + 1}}. {{question.text}}<span class="required" v-if="!!question.required">*</span></div>
            <div class="question-reply" v-if="!question.options">
                <input v-if="!question.multi && !question.type" class="color-black" type="text" :value="answers[question.name]" :name="question.name" 
                    :required="!!question.required" placeholder="Enter text here" @change="setAnswer($event, question.name)" :disabled="!editable">
                <input v-if="!question.multi && question.type === 'date'" type="date" class="form-control" :value="answers[question.name]" :name="question.name" 
                    :required="!!question.required" @change="setAnswer($event, question.name)" :disabled="!editable">
                <textarea v-if="question.multi" type="text" :value="answers[question.name]" :name="question.name" 
                    :required="!!question.required" placeholder="Enter text here" @change="setAnswer($event, question.name)" :disabled="!editable"></textarea>
            </div>
            <div class="question-option" v-for="(option, index) in question.options" :key="index">
                <label>
                    <span v-if="!question.multi && question.other" class="circle"></span>
                    <input type="radio" v-if="!question.multi && !question.other" v-model="question.selected" :name="question.name" 
                        :required="!!question.required" :value="(option && option.constructor.name === 'Object') ? option.text : option"
                        :disabled="!editable"> 
                    <input type="checkbox" v-if="question.multi" :name="question.name + ('[' + index + ']')" :required="!!question.required" 
                        :checked="answers[question.name] ? answers[question.name].indexOf((option && option.constructor.name === 'Object') ? option.text : option) >= 0 : false" 
                        :value="(option && option.constructor.name === 'Object') ? option.text : option"
                        @change="toggleChecked($event, question.name, ((option && option.constructor.name === 'Object') ? option.text : option))"
                        :disabled="!editable"> 
                    <span>{{(option && option.constructor.name === 'Object') ? option.text : option}}</span>
                    <input class="width-200 color-black" v-if="!!option.editable && !question.multi && (question.selected === option.text)" 
                        v-model="question.other" type="text" :name="question.name" :required="!!question.required" 
                        placeholder="Enter text here" :disabled="!editable">
                    <input class="width-200 color-black" v-if="!!option.editable && !!question.multi && ((answers[question.name] || []).indexOf(option.text) >= 0)" 
                        v-model="question.other" type="text" :name="question.name + ('[' + index + ']')" :required="!!question.required" placeholder="Enter text here"
                        :disabled="!editable">
                </label>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'questionnaire',
        props: {
            editable: {
                type: Boolean,
                default: true
            },
            questions: {
                type: Array,
                required: true
            },
            'class-name': String
        },
        data() {
            return {
                answers: window.answers || {}
            }
        },
        methods: {
            toggleChecked(e, name, value) {
                if (Array.isArray(this.answers[name])) {
                    if (e.target.checked && this.answers[name].indexOf(value) < 0) this.answers[name].push(value)
                    else {
                        this.answers[name].splice(this.answers[name].indexOf(value), 1)
                    }
                }
                this.$forceUpdate()
            },
            setAnswer(e, name) {
                this.answers[name] = e.target.value
            }
        },
        mounted() {
            if (!this.questions || !Array.isArray(this.questions)) {
                throw new Error('[questions] prop value must be an array')
            }
            this.questions.forEach(question => {
                if (question.options) {
                    if (!question.multi) {
                        question.selected = this.answers[question.name]
                    }
                    else {
                        this.answers[question.name] = this.answers[question.name] || []
                    }
                }
            })
        }
    }
</script>

<style>
    label {
        width: 100%;
    }

    label input[type='text'] {
        width: 200px;
    }

    .required {
        color: red;
    }

    .circle {
        border: 1px solid #ddd;
        border-radius: 50%;
        width: 15px;
        height: 15px;
        display: inline-block;
    }

    input.color-black {
        color: black !important;
    }
</style>