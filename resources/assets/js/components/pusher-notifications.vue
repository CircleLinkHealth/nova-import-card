<template>
    <div>
        <ul>
            <li v-for="test in tests" v-text="test">

            </li>
        </ul>
        <input type="text" v-model="newTest" @blur="addTest">
    </div>
</template>

<script>
    export default {
        name: "pusher-notifications",

        data() {
            return {
                tests: [],
                newTest: '',
            }
        },

        methods: {
            addTest() {
                axios.post('/pusher-test', {body: this.newTest});
                this.tests.push(this.newTest);
                this.newTest = '';
            }
        },

        created() {
            axios.get('/pusher-test').then(response => (this.tests = response.data));
        }
    }
</script>

<style scoped>

</style>