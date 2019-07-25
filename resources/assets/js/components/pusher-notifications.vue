<template>
    <div>
        <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
             aria-expanded="false" style="padding: inherit;"><i class="glyphicon glyphicon-bell"></i> Notifications
            <span v-show="shouldShowCount" class="badge badge-secondary">{{count}}</span></div>

        <ul class="dropdown-menu" role="menu">
            <li class="dropdown-header">NOTIFICATIONS</li>
            <li v-for="notification in notifications">
                <a @click="redirectAndMarkAsRead(notification)">
                    {{notification.sender_id}} {{notification.subject}} {{notification.patient_id}}
                </a>
            </li>
        </ul>
    </div>
</template>

<script>

    export default {
        name: "pusher-notifications",
        components: {},
        props: [
            'user'
        ],

        data() {
            return {
                notifications: [],
                notificationsModel: [],
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

            isMarkedAsUnread() {

            },

        },
        methods: {
            redirectAndMarkAsRead(notification) {
                axios.post(`/redirect-addendum/${notification.receiver_id}/${notification.attachment_id}`)
                    .then(response => {
                            this.markAsRead(notification);
                        }
                    );
            },
            markAsRead(notification) {
                window.location.href = notification.redirectTo;
            }
        },

        created() {
            axios.get('/addendum-notifications')
                .then(response => {
                        console.log(response.data);
                        const notificationsModel = response.data[0].map(q => q);
                        const notificationsData = response.data[0].map(q => q.data);
                        this.notificationsModel.push(...notificationsModel)
                        this.notifications.push(...notificationsData)
                    }
                );

//Real Time Notifications
            window.Echo.private('addendum.' + this.authUserId).listen('AddendumPusher', ({dataToPusher}) => {
                this.notifications.push(dataToPusher);
            });
        }
    }

</script>

<style scoped>
    .dropdown-menu {
        cursor: pointer;
        word-spacing: 5px;
        background: #ffffff;
        padding-bottom: unset;
        overflow-y: scroll;
        max-height: 480%;
    }

    .dropdown-menu li > a {
        padding-top: 4%;
        padding-bottom: 4%;
        color: #90949c;
        font-family: Helvetica, Arial, sans-serif;
        background-color: #ffffff;
        border-bottom: 1px solid #90949c;
    }

    .badge-secondary {
        background-color: #e46745;
    }

    .dropdown-header {
        color: #90949c;
        cursor: default;
        font-weight: bold;
        border-bottom: 1px solid #90949c;
    }

</style>