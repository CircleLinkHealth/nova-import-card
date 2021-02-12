<template>
    <modal name="chargeable-services" ref="modal" :info="info" :no-footer="true" class-name="modal-patient-problem">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row form-group">
                        <div class="col-sm-12 form-control margin-5" v-for="service in patientServices"
                             :key="service.id">
                            <label>
                                <input :disabled="!service.allowed"
                                       type="checkbox"
                                       v-model="service.selected"
                                       :value="service.id">
                                <span>{{ service.code }} [{{ service.display_name }}]</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12" v-if="loaders.update">
                    <loader></loader>
                </div>
            </div>
        </template>
    </modal>
</template>

<script>
    import {Event} from 'vue-tables-2'

    import SERVICES from '../../../../../../Sharedvuecomponents/Resources/assets/js/constants/services.types';
    import Modal from '../../../../../../Sharedvuecomponents/Resources/assets/js/admin/common/modal'
    import Loader from '../../../../../../Sharedvuecomponents/Resources/assets/js/components/loader'

    export default {
        name: 'chargeable-services-modal',
        props: ['services'],
        components: {
            'modal': Modal,
            'loader': Loader
        },
        data() {
            const self = this
            return {
                loaders: {
                    update: null
                },
                patientServices: [],
                row: null,
                info: {
                    okHandler() {
                        if (typeof (this.done) == 'function') {
                            const services = self.patientServices
                                .filter(service => service.selected)
                                .map(service => {
                                    return {
                                        id: service.id,
                                        total_time: service.total_time
                                    };
                                });
                            this.done(services);
                        }
                        Event.$emit("modal-chargeable-services:hide")
                    },
                    done: null
                }
            }
        },
        methods: {
            /**
             * Returns true if a patient can be charged for a
             * service. We assume that the services in question here
             * are chargeable for the patient's practice.
             * @param service
             * @returns {boolean}
             */
            isServiceChargeableForPatient: function (service) {
                if (this.isPlusCode(service.code)) {
                    return false;
                }
                switch (service.code) {
                    case SERVICES.CCM:
                    case SERVICES.BHI:
                    case SERVICES.RPM:
                    case SERVICES.GENERAL_CARE_MANAGEMENT:
                        return service.total_time >= 1200;
                    case SERVICES.PCM:
                        return service.total_time >= 1800;
                    default:
                        return false;
                }
            },

            isPlusCode(serviceCode) {
                return [SERVICES.CCM40, SERVICES.CCM60, SERVICES.RPM40, SERVICES.RPM60].indexOf(serviceCode) > -1;
            }
        },
        mounted() {
            Event.$on('modal-chargeable-services:show', (modal) => {
                this.row = (modal || {}).row
                this.info.done = (this.row || {}).onChargeableServicesUpdate
                this.patientServices = this.services.map(service => {
                    const pService = this.row.chargeable_services.find((item) => service.id === item.id);
                    service.selected = !!pService;
                    service.total_time = pService ? pService.total_time : 0;
                    service.allowed = this.isServiceChargeableForPatient(service);

                    return service;
                })
            })
        }
    }
</script>

<style>
input[type='checkbox'] {
    display: inline !important;
}

input[type='checkbox'][disabled] + span {
    color: #9e9e9e;
}

.margin-5 {
    margin: 5px;
}
</style>
