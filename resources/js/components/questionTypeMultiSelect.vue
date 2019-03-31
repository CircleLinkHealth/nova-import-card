<template>
    <div class="custom-checkbox">
        <div v-for="checkBox in checkBoxValues">
            <label>{{checkBox.value}}
                <input class="multi-select"
                       type="checkbox"
                       name="checkboxTypeAnswer">
            </label>
        </div>
    </div>
</template>

<script>
    export default {
        name: "questionTypeMultiSelect",
        props: ['question'],
        components: {},

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers,
                checkBoxOptions:[],
            }
        },
        computed: {

        },

        created() {
            const options = this.question.type.question_type_answers.map(q => q.options);
            this.checkBoxOptions.push(...options);
        },
    }
</script>

<style scoped>

</style>