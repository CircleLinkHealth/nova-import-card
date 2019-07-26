<template>
    <div>
        <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
             aria-expanded="false" style="padding: inherit;"><i class="glyphicon glyphicon-bell"></i> Notifications
            <span v-show="shouldShowCount" class="badge badge-secondary">{{count}}</span></div>

        <ul class="dropdown-menu" role="menu">
            <li class="dropdown-header">NOTIFICATIONS</li>
            <li v-for="notification in notifications">
                <a :class="{greyOut: notification.read_at !== undefined && notification.read_at !== null}"
                        @click="redirectAndMarkAsRead(notification)">
                    {{notification.data.sender_id}} {{notification.data.subject}} {{notification.data.patient_id}}
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
                notificationsFromPusher: [],
                notificationsFromDb: [],
                authUserId: this.user.id,
                count: '',
            }
        },
        computed: {
            shouldShowCount() {
                return this.countUnreadNotifications !== 0;
            },

            countUnreadNotifications() {
                // NotificationsFromPusher is always unread - it doesnt have property: "read_at"
                const notificationsFromPusher = this.notificationsFromPusher.length;
                const count = this.notificationsFromDb.filter(q=>q.read_at === null).length;
                const sum = count + notificationsFromPusher;
                this.count = sum ;

                return sum;

            },

            notifications() {
                return this.notificationsFromDb.concat(this.notificationsFromPusher);
            }

        },
        methods: {
            redirectAndMarkAsRead(notification) {
                axios.post(`/redirect-addendum/${notification.data.receiver_id}/${notification.data.attachment_id}`)
                    .then(response => {
                            this.markAsRead(notification);
                        }
                    );
            },

            markAsRead(notification) {
                window.location.href = notification.data.redirectTo;
            }
        },

        created() {
            axios.get('/addendum-notifications')
                .then(response => {
                        const notificationsFromDb = response.data[0].map(q => q);
                        this.notificationsFromDb.push(...notificationsFromDb)

                    }
                );

//Real Time Notifications
            window.Echo.private('addendum.' + this.authUserId).listen('AddendumPusher', ({dataToPusher}) => {
                this.notificationsFromPusher.push(dataToPusher);
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

    .greyOut {
        opacity: 0.6;
    }

</style>