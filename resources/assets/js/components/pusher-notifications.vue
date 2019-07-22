<template>
    <div>
        <i class="glyphicon glyphicon-bell"></i> Notifications
        <span v-show="shouldShowCount" class="badge badge-secondary" style="background-color: #e46745;">{{count}}</span>
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
                count: '',
            }
        },
        computed: {
            countNotifications() {
                this.count = this.notifications.length;
                return this.notifications.length;
            },

            shouldShowCount() {
                return this.countNotifications !== 0;
            }

        },
        methods: {},

        created() {
            axios.get('/addendum-notifications').then(response => (this.notifications.push(...response.data[0])));

            window.Echo.private('addendum.' + this.authUserId).listen('Pusher', ({dataToPusher}) => {
                this.notifications.push(dataToPusher);
                console.log(dataToPusher);
            });
        }
    }
</script>

<style scoped>

</style>