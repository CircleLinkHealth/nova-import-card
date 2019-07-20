<template>
    <div>
        <ul>
            <li v-for="notification in notifications">
                {{notification}}
            </li>
            <li>Pusher Notifications Test !!!</li>
        </ul>
        <!--        <input type="text" v-model="newnotification" @blur="addnotification">-->
    </div>
</template>

<script>

    export default {
        name: "pusher-notifications",

        props: [
            'user'
        ],

        data() {
            return {
                notifications: [],
                authUserId: this.user.id,
            }
        },

        methods: {},

        created() {
            // axios.get('/pusher-test').then(response => (this.notifications = response.data));

            window.Echo.private('pusher-test.' + this.authUserId).listen('PusherTest', ({dataToPusher}) => {
                this.notifications.push(dataToPusher);
                console.log(dataToPusher);
            });
        }
    }
</script>

<style scoped>

</style>