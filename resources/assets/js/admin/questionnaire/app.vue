<template>
    <div :class="className">
        <div class="form-group" v-for="(question, index) in questions" :key="index">
            <div class="question-text">{{index + 1}}. {{question.text}}<span class="required" v-if="!!question.required">*</span></div>
            <div class="question-reply" v-if="!question.options">
                <input v-if="!question.multi" type="text" :name="question.name" :required="!!question.required" placeholder="Enter text here">
                <textarea v-if="question.multi" type="text" :name="question.name" :required="!!question.required" placeholder="Enter text here"></textarea>
            </div>
            <div class="question-option" v-for="(option, index) in question.options" :key="index">
                <label>
                    <input :type="question.multi ? 'checkbox' : 'radio'" :name="question.name" :required="!!question.required" :value="(option && option.constructor.name === 'Object') ? option.value : option"> 
                    <span>{{(option && option.constructor.name === 'Object') ? option.text : option}}</span>
                    <input class="width-200" v-if="!!option.editable" type="text" :name="question.name" :required="!!question.required" placeholder="Enter text here">
                </label>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'questionnaire',
        props: {
            questions: {
                type: Array,
                required: true
            },
            'class-name': String
        },
        mounted() {
            if (!this.questions || !Array.isArray(this.questions)) {
                throw new Error('[questions] prop value must be an array')
            }
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
</style>