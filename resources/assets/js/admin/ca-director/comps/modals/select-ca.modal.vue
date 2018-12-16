<template>
    <modal name="select-ca" class="modal-select-ca" :no-footer="true" :info="selectCaModalInfo">
        <template slot="title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Assign Care Ambassador to selected Patient(s)</h3>
                </div>
            </div>
        </template>
        <div class="row">
            <p>Select Care Ambassador:</p>
        </div>
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

                this.axios.post(rootUrl('/admin/ca-director/assign-ambassador'), {
                    ambassadorId: this.selectedAmbassador.value,
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

            if (ambassadors != null) {
                this.loading = false;
                this.list = ambassadors;
                return;
            }

            this.getAmbassadors();

        }
    }
</script>

<style>
    .modal-select-ca .modal-wrapper {
        overflow-x: auto;
        white-space: nowrap;
        display: block;
        margin-top: 40px;
        vertical-align: unset;
        margin-left: auto;
        margin-right: auto;
    }

    /*width will be set automatically when modal is mounted*/
    .modal-select-ca .modal-container {
        width: 600px;
    }




    .modal-select-ca .loader {
        position: absolute;
        right: 5px;
        top: 5px;
        width: 20px;
        height: 20px;
    }

    .modal-select-ca .glyphicon-remove {
        width: 20px;
        height: 20px;
        color: #d44a4a;
        vertical-align: middle;
        font-size: 20px;
    }

    .width-90 {
        float: left;
        width: 90%;
    }

    .width-82 {
        float: left;
        width: 82%;
    }

    .width-18 {
        float: left;
        width: 18%;
    }

    .width-10 {
        float: left;
        width: 10%;
    }

    .padding-left-5 {
        padding-left: 5px;
    }

    .padding-top-7 {
        padding-top: 7px;
    }

    span.required {
        color: red;
        font-size: 18px;
        position: absolute;
        top: 2px;
    }

    .dropdown.v-select.form-control {
        height: auto;
        padding: 0;
    }

    .v-select .dropdown-toggle {
        height: 34px;
        overflow: hidden;
    }

    .modal-select-ca .modal-body {
        min-height: 300px;
    }

    .selected-tag {
        width: 80%;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    a.my-tool-tip {
        float: right;
        margin-right: 4px;
    }

</style>