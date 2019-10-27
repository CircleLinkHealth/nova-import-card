<template>
    <div class="container">
        <div class="title"><h2>YOUR NOTIFICATIONS</h2></div>
        <div class="list-group list-group-flush notifications">
            <div v-for="notification in notifications">
                <a class="list-group-item list-group-item-action"
                   :class="{greyOut: notification.read_at !== undefined && notification.read_at !== null}"
                   @click="redirectAndMarkAsRead(notification)"
                   v-html="showAll(notification)">
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    // import PusherNotifications from './pusher-notifications';
    export default {
        name: "pusher-see-all-notifications",
        components: {
            // 'pusher-notifications': PusherNotifications,
        },
        props: [
            'notifications'
        ],

        data() {
            return {

            }
        },

        methods: {
            showAll(notification) {
                const getSenderName = notification.data.sender_name;
                const getNotificationSubject = notification.data.subject;
                const getPatientName = notification.data.patient_name;
                const getNotificationElapsedTime = notification.elapsed_time;

                return `<strong>${getSenderName}</strong> ${getNotificationSubject}<strong> ${getPatientName}</strong>
                        <br><div style="padding-top: 1%">${getNotificationElapsedTime}</div>`;

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
        }
    }
</script>

<style scoped>

    .notifications {
        overflow-y: scroll;
        height: 85%;
    }

    .title {
        color: black;
        font-weight: bold;
        margin-bottom: 2%;
        margin-top: 8%;
        margin-left: 1%;
    }

    a.list-group-item {
        border-left: unset;
        border-right: unset;
        padding: 1.5%;
        font-size: initial;
    }

    a.list-group-item:hover {
        background: #ecf8ff;
    }

    .greyOut {
        opacity: 0.6;
    }

</style>