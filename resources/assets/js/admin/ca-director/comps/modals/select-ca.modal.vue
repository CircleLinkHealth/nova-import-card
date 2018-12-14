<template>
    <modal name="select-ca" :no-title="true" :no-footer="true">
        <template slot-scope="props">
            <div class="row">
                <select>
                    <div class="row" v-for="user in ambassadors" :key="user.id">
                        <div class="col-sm-1">
                            <option v-show="user.display_name">{patient.display_name}</option>
                        </div>
                    </div>
                </select>
            </div>
        </template>
    </modal>
</template>

<script>
    import Modal from '../../../common/modal'
    import {Event} from 'vue-tables-2'
    import {rootUrl} from '../../../../app.config'
    import Notifications from '../../../../components/notifications'
    // import Loader from '../../../../components/loader'
    import VueCache from '../../../../util/vue-cache'

    export default {
        name: "select-ca-modal",
        props: {
            selectedPatients: {
                type: Array,
                required: true
            },
            ambassadors: {
                type: Promise,
                required: true
            },
        },
        components: {
            'modal': Modal,
            'notifications': Notifications,
            // 'loader': Loader
        },
    }
</script>

<style scoped>

</style>