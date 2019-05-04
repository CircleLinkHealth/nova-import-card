<template>
    <div>
        {{question.id}}{{'.'}} {{question.body}}
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