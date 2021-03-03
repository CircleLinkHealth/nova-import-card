<template>
    <modal name="chargeable-services" ref="modal" :info="info" :no-footer="true" class-name="modal-patient-problem">
        <template slot-scope="props">
            <div class="row">
                <div class="col-sm-12">
                    <div class="row form-group">
                        <div class="col-sm-12 form-control margin-5" v-for="service in patientServices"
                             :key="service.id">
                            <label>
                                <input :disabled="service.disabled"
                                       type="checkbox"
                                       @change="onCheck"
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
            <div class="row">
                <span v-if="validationMessage"><span style="font-weight: 700; color: red">&#33;</span> {{ validationMessage }}</span>
            </div>
        </template>
    </modal>
</template>

<script>
    import {Event} from 'vue-tables-2'

    import SERVICES from '../../../../../../Sharedvuecomponents/Resources/assets/js/constants/services.types';
    import SERVICE_CLASHES from '../../../../../../Sharedvuecomponents/Resources/assets/js/constants/services.clashes';
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
                changes: [],
                row: null,
                validationMessage: null,
                billingRevampEnabled: false,
                info: {
                    okHandler() {
                        if (typeof (this.done) == 'function' && self.changes.length) {
                            this.done(self.patientServices, self.changes);
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
            },

            onCheck(event) {
                if (!event) {
                    return;
                }
                const service = this.patientServices.find(s => s.id === +event.currentTarget.value);
                if (!service) {
                    return;
                }

                const changedIndex = this.changes.findIndex(changed => changed.id === service.id);
                if (changedIndex > -1) {
                    this.changes.splice(changedIndex, 1);
                } else {
                    this.changes.push({id: service.id, action_type: service.selected ? 'force' : 'block'});
                }

                this.checkForClashes();
            },

            checkForClashes() {
                const clashing = new Set();
                const selected = this.patientServices.filter(s => s.selected);
                selected.forEach(service => {
                    const clashingServices = SERVICE_CLASHES[service.code];
                    if (!clashingServices) {
                        return;
                    }

                    selected.forEach(s2 => {
                        if (clashingServices.indexOf(s2.code) > -1) {
                            clashing.add(service.code);
                            clashing.add(s2.code);
                        }
                    })
                });

                if (!clashing.size) {
                    this.validationMessage = null;
                    return;
                }

                const str = Array.from(clashing).join(', ');
                this.validationMessage = `Your selection of services is invalid, there are clashing services[${str}]. Please revise.`;
            }
        },
        mounted() {
            Event.$on('modal-chargeable-services:show', (modal) => {
                this.billingRevampEnabled = (modal || {}).billing_revamp_enabled
                this.row = (modal || {}).row
                this.info.done = (this.row || {}).onChargeableServicesUpdate
                this.patientServices = this.services.map(service => {
                    const pService = this.row.chargeable_services.find((item) => service.id === item.id);
                    service.selected = pService && pService.is_fulfilled;
                    service.total_time = pService ? pService.total_time : 0;
                    service.disabled = this.billingRevampEnabled ? !this.isServiceChargeableForPatient(service) :  false;

                    return service;
                });
            });
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
