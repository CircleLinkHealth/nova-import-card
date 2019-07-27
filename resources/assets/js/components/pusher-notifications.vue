<template>
    <div>
        <div class="dropdown-toggle top-nav-item" data-toggle="dropdown" role="button"
             aria-expanded="false" style="padding: inherit;"><i class="glyphicon glyphicon-bell"></i> Notifications
            <span v-show="shouldShowCount" class="badge badge-secondary">{{count}}</span></div>

        <div class="dropdown-menu">
            <div class="dropdown-header">
                NOTIFICATIONS
            </div>

            <div class="dropdown-content">
                <div v-for="notification in notifications"
                     class="dropdown-item"
                     :class="{greyOut: notification.read_at !== undefined && notification.read_at !== null}"
                     @click="redirectAndMarkAsRead(notification)">
                    {{show(notification)}}
                </div>
            </div>

            <div class="dropdown-footer">
                See All
            </div>

        </div>
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
                name: '',
                senderName: ''
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
                const getSenderName = this.setSenderName(notification);
                const getNotificationSubject = this.getNotificationSubject(notification);
                const getPatientName = this.setPatientName(notification);

                const showIfDataFromDb = getSenderName + ' ' + getNotificationSubject + ' ' + getPatientName;
                const showIfDataFromPusher = this.senderName + ' ' + getNotificationSubject + ' ' + this.name;

                if (getSenderName !== undefined && getPatientName !== undefined) {
                    return showIfDataFromDb;
                } else {
                    return showIfDataFromPusher;
                }

            },

            setPatientName(notification) {
                return notification.data.hasOwnProperty('patient_name') ? notification.data.patient_name : this.getPatientName(notification);

            },
            getPatientName(notification) {
                axios.get(`/getName/${notification.data.patient_id}`).then(response => {
                    this.name = response.data.name;
                });
            },
            getNotificationSubject(notification) {
                return notification.data.subject;
            },

            setSenderName(notification) {
                return notification.data.hasOwnProperty('sender_name') ? notification.data.sender_name : this.getSenderName(notification);
            },

            getSenderName(notification) {
                axios.get(`/getSenderName/${notification.data.sender_id}`).then(response => {
                    this.senderName = response.data.senderName;
                });

            },

            redirectAndMarkAsRead(notification) {
                axios.post(`/redirect-mark-read/${notification.data.receiver_id}/${notification.data.attachment_id}`)
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
                        const notificationsFromDb = response.data[0];
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
        width: 217%;
    }

    .dropdown-content {
        overflow-y: scroll;
        max-height: 280px;
    }

    .dropdown-item {
        padding: 5%;
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

    .dropdown-footer {
        color: #00bfff;
        text-align: center;
        padding-bottom: 2%;
        border-top: 1px solid;
    }

    .greyOut {
        opacity: 0.6;
    }

</style>