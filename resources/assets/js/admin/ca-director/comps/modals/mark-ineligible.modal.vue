<template>
    <modal name="select-ca" :no-title="true" :no-footer="true" :info="selectCaModalInfo">
        <div class="row">
            <p>
                Are you sure you want to mark the Enrolees as ineligible?
                (they will be no longer be shown in the table)
            </p>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <notifications ref="notificationsComponent" name="select-ca-modal"></notifications>
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
        name: "mark-ineligible-modal",
        props: {
            selectedEnrolleeIds: {
                type: Array,
                required: true
            },
        },
        data: () => {
            return {
                loading: false,
                selectCaModalInfo: {
                    okHandler: () => {
                        Event.$emit('notifications-select-ca-modal:dismissAll');
                        self.markEnrolleesAsIneligible();
                    },
                    cancelHandler: () => {
                        Event.$emit('notifications-select-ca-modal:dismissAll');
                        Event.$emit("modal-select-ca:hide");
                    }
                }
            }
        },
        methods: {

            markEnrolleesAsIneligible() {

                this.loading = true;

                this.axios.post(rootUrl('/admin/ca-director/mark-ineligible'), {
                    enrolleeIds: this.selectedEnrolleeIds
                })
                    .then(resp => {
                        this.loading = false;
                        Event.$emit("modal-select-ca:hide");
                        this.$parent.$refs.table.refresh();
                    })
                    .catch(err => {
                        this.loading = false;
                        Event.$emit('notifications-select-ca-modal:create', {
                            noTimeout: true,
                            text: err.message,
                            type: 'error'
                        });
                    });
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

            return;

        }
    }
</script>

<style scoped>

</style>