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
    export default {
        name: "pusher-see-all-notifications",
        components: {

        },
        props: [
            'notifications'
        ],

        data() {
            return {

            }
        },
        // @todo: im using the same methods as pusher-notifications.vue
        methods: {
            showAll(notification) { //this is the same function as in pusher-notifications.vue, it should be  extracted
                const getNotificationSubject = notification.data.subject;
                const getNotificationElapsedTime = notification.elapsed_time;
                return `${getNotificationSubject}
                        <br><div style="padding-top: 1%">${getNotificationElapsedTime}</div>`;

            },

            redirectAndMarkAsRead(notification) {
                axios.post(`/redirect-mark-read/${notification.id}`)
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
        cursor: pointer;
    }

    .greyOut {
        opacity: 0.6;
    }

</style>