<template>
    <modal name="select-ca" :no-title="true" :no-footer="true" :info="selectCaModalInfo">
        <div class="row">
            <v-select max-height="200px" class="form-control" v-model="selectedAmbassador"
                      :options="list">
            </v-select>
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

    let ambassadors = null;
    let self;

    export default {
        name: "select-ca-modal",
        props: {
            selectedEnrolleeIds: {
                type: Array,
                required: true
            },
        },
        data: () => {
            return {
                loading: false,
                list: [],
                selectedAmbassador: null,
                selectCaModalInfo: {
                    okHandler: () => {
                        Event.$emit('notifications-select-ca-modal:dismissAll');
                        self.assignEnrolleesToAmbassador();
                    },
                    cancelHandler: () => {
                        Event.$emit('notifications-select-ca-modal:dismissAll');
                        Event.$emit("modal-select-ca:hide");
                    }
                }
            }
        },
        methods: {

            getAmbassadors() {
                return this.axios
                    .get(rootUrl('/admin/ca-director/ambassadors'))
                    .then(response => {
                        this.loading = false;
                        ambassadors = response.data;
                        this.list = ambassadors.map(x => {
                            return {label: x.display_name, value: x.id};
                        });
                        return this.list;
                    })
                    .catch(err => {
                        this.loading = false;
                    });
            },
            assignEnrolleesToAmbassador() {

                this.loading = true;

                this.axios.post('', {
                    ambassadorId: this.selectedAmbassador.value,
                    enrolleeIds: this.enrolleeIds
                })
                    .then(resp => {
                        this.loading = false;
                        Event.$emit("modal-select-ca:hide");
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

            if (ambassadors != null) {
                this.loading = false;
                this.list = ambassadors;
                return;
            }

            this.getAmbassadors();

        }
    }
</script>

<style scoped>

</style>