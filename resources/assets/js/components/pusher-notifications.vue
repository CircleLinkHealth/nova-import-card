<template>
    <div>
        <span v-show="shouldShowCount" class="badge badge-secondary">{{ count }}</span>
        <loader v-show="waiting"></loader>
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
                     @click="seeAll()">
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
// import PusherSeeAllNotifications from './pusher-see-all-notifications';
import LoaderComponent from '../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/components/loader'

export default {
    name: "pusher-notifications",
    components: {
        'loader': LoaderComponent,
    },
    props: [
        'userId'
    ],

    data() {
        return {
            notificationsFromPusher: [],
            notificationsFromDb: [],
            authUserId: this.userId,
            count: '',
            patientName: '',
            senderName: '',
            component: '',
            isClicked: false,
            notificationsFromDbCount: [],
            waiting: false,
        }
    },
    computed: {
        shouldShowCount() {
            return this.countUnreadNotifications !== 0 && this.waiting !== true;
        },

        countUnreadNotifications() {
            // NotificationsFromPusher is always unread - it doesnt have property: "read_at"
            const notificationsFromPusher = this.notificationsFromPusher.length;
            const count = this.notificationsFromDbCount[0];
            const sum = count + notificationsFromPusher;
            this.count = sum;
            return sum;
        },

        notifications() {
            return this.notificationsFromPusher.concat(this.notificationsFromDb);
        },

    },
    methods: {
        show(notification) {
            const notificationSubject = notification.data.subject;
            const notificationElapsedTime = notification.elapsed_time;
            //@todo: use normal classes in html here
            return `${notificationSubject}<br>
                        <span style="float: right; color: #90949c">${notificationElapsedTime}</span>`;

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

        seeAll() {
            window.location.href = '/see-all-notifications'
        },

    },

    created() {
        this.waiting = true;
        axios.get('/notifications')
            .then(response => {
                const notificationsFromDb = response.data;
                this.waiting = false;
                if (!notificationsFromDb) {
                    return;
                }

                if (Array.isArray(notificationsFromDb.notifications)) {
                    this.notificationsFromDb.push(...notificationsFromDb.notifications);
                }

                this.notificationsFromDbCount.push(notificationsFromDb.totalCount);
            })
            .catch(err => {
                console.error(err);
            });
        if (window.EchoPusher !== null && window.EchoPusher !== undefined) {
            window.EchoPusher.private('App.User.' + this.authUserId)
                .notification((notification) => {
                    axios.get(`/notifications/${notification.id}`).then(response => {
                            this.notificationsFromPusher.push(response.data)
                        }
                    );
                });
        }

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
    z-index: 100;
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

.loader {
    width: 14px;
    height: 14px;
    border: 5px solid #e46745;
    border-top: 5px solid #555;
}
</style>
