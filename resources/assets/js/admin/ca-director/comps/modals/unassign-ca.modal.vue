<template>
    <modal name="unassign-ca" class="modal-unassign-ca" :no-title="true" :no-footer="true" :info="unassignCaModalInfo">
        <div class="row">
            <p>
                You have selected <b>{{this.enrolleeCount()}}</b> patient(s).
                <br>
                <br>
                <br>
                <b>Warning:</b>
                Are you sure you want to unassign these patients from their current Care Ambassadors?
            </p>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <notifications ref="notificationsComponent" name="unassign-ca-modal"></notifications>
            </div>
        </div>
        <loader v-if="loading"/>
    </modal>
</template>

<script>
    import {rootUrl} from '../../../../app.config.js';
    import Modal from '../../../common/modal';
    import Notifications from '../../../../components/notifications';
    import Loader from '../../../../components/loader';
    import VueSelect from 'vue-select';
    import {Event} from 'vue-tables-2'

    let self;

    export default {
        name: "unassign-ca.modal",
        props: {
            selectedEnrolleeIds: {
                type: Array,
                required: true
            },
        },
        data: () => {
            return {
                loading: false,
                unassignCaModalInfo: {
                    okHandler: () => {
                        Event.$emit('notifications-unassign-ca-modal:dismissAll');
                        self.unassignCaFromEnrollees();
                    },
                    cancelHandler: () => {
                        Event.$emit('notifications-unassign-ca-modal:dismissAll');
                        Event.$emit("modal-unassign-ca:hide");
                    }
                }
            }
        },
        methods: {

            unassignCaFromEnrollees() {

                this.loading = true;

                this.axios.post(rootUrl('/admin/ca-director/unassign-ca'), {
                    enrolleeIds: this.selectedEnrolleeIds
                })
                    .then(resp => {
                        this.loading = false;
                        Event.$emit("modal-unassign-ca:hide");
                        this.$parent.$refs.table.refresh();
                    })
                    .catch(err => {
                        this.loading = false;
                        Event.$emit('notifications-unassign-ca-modal:create', {
                            noTimeout: true,
                            text: err.message,
                            type: 'error'
                        });
                    });
            },
            enrolleeCount(){
                return this.selectedEnrolleeIds.length;
            }
        },
        components: {
            'modal': Modal,
            'notifications': Notifications,
            'loader': Loader,
            'v-select': VueSelect
        },
        mounted: function () {
            self = this;

            this.loading = false;
        }
    }
</script>

<style scoped>

</style>