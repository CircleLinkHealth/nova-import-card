<style>
    .vue-notification p {
    margin-right: 20px;
    }

    .vue-notification-slide-in-out {
        -webkit-transition: all 800ms cubic-bezier(0.680, 0, 0.265, 1); /* older webkit */
        -webkit-transition: all 800ms cubic-bezier(0.680, -0.550, 0.265, 1.550);
        -moz-transition: all 800ms cubic-bezier(0.680, -0.550, 0.265, 1.550);
        -o-transition: all 800ms cubic-bezier(0.680, -0.550, 0.265, 1.550);
        transition: all 800ms cubic-bezier(0.680, -0.550, 0.265, 1.550); /* easeInOutBack */
    }
</style>

<template>
    <transition
            name="custom-classes-transition"
            enter-active-class="animated slideInRight"
            leave-active-class="animated slideOutRight"
    >
        <div class="alert vue-notification animated" :class="notification.type ? 'alert-' + notification.type : 'secondary'">
            <button v-if="!notification.timeout" @click="removeNotification(notification)" class="close" aria-label="Close alert" type="button">×
            </button>
            <h5 v-if="notification.title">{{notification.title}}</h5>
            <p>
                {{notification.text}}
            </p>
        </div>
    </transition>
</template>

<script>
    /**
     * Notification Component
     */
    import {mapActions} from 'vuex'
    import {removeNotification} from '../../../../../../../../resources/assets/js/store/actions'

    export default {
        props: ['notification'],

        data () {
            return {timer: null}
        },

        mounted () {
            let timeout = this.notification.hasOwnProperty('timeout') ? this.notification.timeout : true
            if (timeout) {
                let delay = this.notification.delay || 3000
                this.timer = setTimeout(function () {
                    this.removeNotification(this.notification)
                }.bind(this), delay)
            }
        },

        methods: Object.assign({},
            mapActions(['removeNotification'])
        )
    }
</script>