<template>
    <div>
        <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
             aria-expanded="false" style="padding: inherit;"><i class="glyphicon glyphicon-bell"></i> Notifications
            <span v-show="shouldShowCount" class="badge badge-secondary"
                  style="background-color: #e46745;">{{count}}</span></div>

        <ul class="dropdown-menu" role="menu" style="background: white !important;">
            <li v-for="notification in notifications">
                <a>{{notification.sender_id}} {{notification.subject}} {{notification.patient_id}}</a>
            </li>
        </ul>
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
            },

        },
        methods: {},

        created() {
            axios.get('/addendum-notifications')
                .then(response => {
                        const notificationsData = response.data[0].map(q => q.data);
                        this.notifications.push(...notificationsData)
                    }
                );

            window.Echo.private('addendum.' + this.authUserId).listen('Pusher', ({dataToPusher}) => {
                this.notifications.push(dataToPusher);
            });
        }
    }
</script>

<style scoped>

</style>