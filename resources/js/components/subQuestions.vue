<template>
    <div>
        {{question.conditions.id}}{{'.'}} {{question.conditions.body}}
        <div v-for="label in question.conditions.labels" class="question-answer-type" :key="question.conditions.id">
            <label :for="question.conditions.id">{{label}}</label>
            <br>
            <input type="text" name="textTypeAnswer">
        </div>
    </div>
</template>

<script>
    export default {
        name: "subQuestions",
        props: ['question'],
    }
</script>

<style scoped>

</style>