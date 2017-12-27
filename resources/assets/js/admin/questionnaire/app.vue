<template>
    <div :class="className">
        <div class="form-group" v-for="(question, pIndex) in questions" :key="pIndex">
            <div class="question-text">{{pIndex + 1}}. {{question.text}}<span class="required" v-if="!!question.required">*</span></div>
            <div class="question-reply" v-if="!question.options">
                <input v-if="!question.multi" type="text" :name="question.name" :required="!!question.required" placeholder="Enter text here">
                <textarea v-if="question.multi" type="text" :name="question.name" :required="!!question.required" placeholder="Enter text here"></textarea>
            </div>
            <div class="question-option" v-for="(option, index) in question.options" :key="index">
                <label>
                    <span v-if="question.other" class="circle"></span>
                    <input type="radio" v-if="!question.multi && !question.other" v-model="question.selected" :name="question.name" :required="!!question.required" :value="(option && option.constructor.name === 'Object') ? option.text : option"> 
                    <input type="checkbox" v-if="question.multi" :name="question.name + ('[' + index + ']')" :required="!!question.required" :value="(option && option.constructor.name === 'Object') ? option.value : option"> 
                    <span>{{(option && option.constructor.name === 'Object') ? option.text : option}}</span>
                    <input class="width-200" v-if="question.selected === option.text && !!option.editable" v-model="question.other" type="text" :name="question.name" :required="!!question.required" placeholder="Enter text here">
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

    .circle {
        border: 1px solid #ddd;
        border-radius: 50%;
        width: 15px;
        height: 15px;
        display: inline-block;
    }
</style>