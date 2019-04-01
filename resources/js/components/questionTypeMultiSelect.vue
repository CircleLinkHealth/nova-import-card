<template>
    <div class="custom-checkbox">
        <div v-for="checkBoxOption in multiSelectOptions">
            <label>{{checkBoxOption}}
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
                checkBoxValues: this.question.type.question_type_answers[0].value,
                checkBoxOptions: [],
                multiSelectOptions: [],
            }
        },
        computed: {
            placeHolder() {
                return this.checkBoxOptions[0].placeholder
            }
        },

        created() {
            const options = this.question.type.question_type_answers.map(q => q.options);
            const multiSelect = options.flatMap(q => q.multi_select_options);

            this.checkBoxOptions.push(...options);
            this.multiSelectOptions.push(...multiSelect);


        },
    }
</script>

<style scoped>

</style>