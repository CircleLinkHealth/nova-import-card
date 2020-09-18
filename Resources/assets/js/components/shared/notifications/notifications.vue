<style>
    .vue-notifications {
        position: fixed;
        right: 3%;
        top: 6%;
        width: 350px;
        z-index: 1;
    }

</style>

<template>
    <div class="vue-notifications">
        <notification v-for="notification in notifications" :notification="notification" :key="getRandomId">
        </notification>
    </div>
</template>

<script>
    import Notification from './notification.vue'
    import {mapGetters} from 'vuex'
    import {notifications} from '../../../../../../../../resources/assets/js/store/getters'

    export default {
        components: {
            notification: Notification
        },

        computed: Object.assign(
            mapGetters({
                notifications: 'notifications'
            }),
            {
                getRandomId() {
                    return _.random(100)
                }
            }
        ),


    }
</script>