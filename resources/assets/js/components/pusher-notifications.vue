<template>
    <div>
        <ul>
            <li v-for="message in messages" v-text="message">

            </li>
        </ul>
        <input type="text" v-model="newMessage" @blur="addMessage">
    </div>
</template>

<script>
    export default {
        name: "pusher-notifications",

        data() {
            return {
                messages: [],
                newMessage: '',
            }
        },

        methods: {
            addMessage() {
                axios.post('/pusher-test', {body: this.newMessage});
                this.messages.push(this.newMessage);
                this.newMessage = '';
            }
        },

        created() {
            axios.get('/pusher-test').then(response => (this.messages = response.data));

            window.Echo.channel('pusher-test' + this.project.id).listen('PusherTest', ({message}) => {
                this.messages.push(message.body);
            });
        }
    }
</script>

<style scoped>

</style>