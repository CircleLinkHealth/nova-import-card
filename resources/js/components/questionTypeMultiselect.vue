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
        name: "questionTypeCheckbox",
        props: ['question'],
        components: {},

        data() {
            return {
                checkBoxValues: this.question.type.question_type_answers,
            }
        },
        computed: {},

        created() {

        },
    }
</script>

<style scoped>

</style>