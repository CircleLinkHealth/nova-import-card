<template>
    <div>
        <span v-show="shouldShowCount" class="badge badge-secondary">{{count}}</span>
        <div v-if="!isClicked">
            <div class="dropdown-menu">
                <div class="dropdown-header">
                    NOTIFICATIONS
                </div>
                <!---->
                <div class="dropdown-content">
                    <div v-for="notification in notifications"
                         class="dropdown-item"
                         :class="{greyOut: notification.read_at !== undefined && notification.read_at !== null}"
                         @click="redirectAndMarkAsRead(notification)"
                         v-html="show(notification)">
                    </div>
                </div>
                <div class="dropdown-footer"
                     @click="showAll(notifications)">
                    <a>
                        See All
                    </a>
                </div>
                <!---->
            </div>
        </div>
    </div>
</template>


<script>
    import PusherSeeAllNotifications from './pusher-see-all-notifications.vue';

    export default {
        name: "pusher-notifications",
        components: {
            'pusher-see-all-notifications': PusherSeeAllNotifications,
        },
        props: [
            'user'
        ],

        data() {
            return {
                notificationsFromPusher: [],
                notificationsFromDb: [],
                authUserId: this.user.id,
                count: '',
                patientName: '',
                senderName: '',
                component: '',
                isClicked: false,
            }
        },
        computed: {
            shouldShowCount() {
                return this.countUnreadNotifications !== 0;
            },

            countUnreadNotifications() {
                // NotificationsFromPusher is always unread - it doesnt have property: "read_at"
                const notificationsFromPusher = this.notificationsFromPusher.length;
                const count = this.notificationsFromDb.filter(q => q.read_at === null).length;
                const sum = count + notificationsFromPusher;
                this.count = sum;
                return sum;
            },

            notifications() {
                return this.notificationsFromDb.concat(this.notificationsFromPusher);
            },

        },
        methods: {
            show(notification) {
                const getSenderName = notification.data.sender_name;
                const getNotificationSubject = notification.data.subject;
                const getPatientName = notification.data.patient_name;

                return `<strong>${getSenderName}</strong> ${getNotificationSubject}<strong> ${getPatientName}</strong>
                        <span style="float: right;padding-top: 4%; color: #90949c"></span>`;

            },

            redirectAndMarkAsRead(notification) {
                axios.post(`/redirect-mark-read/${notification.data.receiver_id}/${notification.data.attachment_id}`)
                    .then(response => {
                            this.redirectTo(notification);
                        }
                    );
            },

            redirectTo(notification) {
                window.location.href = notification.data.redirect_link;
            },

            showAll(notifications) {
                // this.isClicked = true;
                // this.component = 'pusher-see-all-notifications';
            },

        },

        created() {
            axios.get('/notifications')
                .then(response => {
                        const notificationsFromDb = response.data;
                        this.notificationsFromDb.push(...notificationsFromDb)
                    }
                );

            // const userId = 13251;
            // window.Echo.private('App.User.' + userId)
            //     .notification((notification) => {
            //         console.log(notification);
            //     });
            //
            //
            // window.Echo.private('users.' + userId)
            //     .notification((notification) => {
            //         console.log(notification);
            //     });
            // Real Time Notifications
            window.Echo.private('notifications.' + 13251).listen('AddendumCreatedEvent', ({dataToPusher}) => {
                axios.get(`/notifications/${dataToPusher.notificationId}`)
                    .then(response => {
                            this.notificationsFromPusher.push(response.data)
                        }
                    );
            })
        }
    }

</script>

<style scoped>
    .dropdown-menu {
        background: #fff;
        border: 1px solid rgba(100, 100, 100, .4);
        border-radius: 0 0 2px 2px;
        box-shadow: 0 3px 8px rgba(0, 0, 0, .25);
        color: #1d2129;
        overflow: visible;
        position: absolute;
        top: 50px;
        width: 430px;
        z-index: 1;
        max-height: 702%;
        word-spacing: 5px;
        padding-bottom: unset;
    }

    .dropdown-menu:before {
        content: '';
        display: block;
        width: 0;
        height: 0;
        color: transparent;
        border: 10px solid #CCC;
        border-color: transparent transparent #FFF;
        margin-top: -27px;
        margin-left: 84%;
    }

    .dropdown-content {
        overflow-y: scroll;
        max-height: 280px;
    }

    .dropdown-item {
        padding: 5%;
        color: #3e3e3ede;
        font-family: Helvetica, Arial, sans-serif;
        background-color: #ffffff;
        border-bottom: 1px solid #90949c;
        cursor: pointer;
    }

    .sex {
        overflow: visible;
        position: absolute;
        height: 1000px;
        width: 1000px;
    }

    .badge-secondary {
        display: inline;
        background: #e46745;
        color: #FFF;
        font-size: 14px;
        font-weight: 400;
        padding: 2px 7px;
        z-index: 1;
    }

    .dropdown-header {
        color: #90949c;
        cursor: default;
        display: block;
        background: #FFF;
        font-weight: bold;
        font-size: 13px;
        padding: 8px;
        margin: 0;
        border-bottom: solid 1px rgba(100, 100, 100, .30);
    }

    .dropdown-footer {
        background: #F6F7F8;
        padding: 8px;
        font-size: 12px;
        font-weight: bold;
        border-top: solid 1px rgba(100, 100, 100, .30);
        text-align: center;
    }

    .dropdown-footer a {
        color: #4fb2e2;
    }

    .dropdown-footer a:hover {
        text-decoration: underline;
    }

    .dropdown-item:hover {
        background: #ecf8ff;
    }

    .greyOut {
        opacity: 0.6;
    }

    .senderName {
        font-weight: bold;
        color: #000;
    }
</style>