<template>
    <modal name="chargeable-services" ref="modal" :info="info" :no-footer="true" class-name="modal-patient-problem">
      <template slot-scope="props">
        <div class="row">
          <div class="col-sm-12">
            <div class="row form-group">
              <div class="col-sm-12 form-control margin-5" v-for="service in patientServices" :key="service.id">
                <label>
                    <input :disabled="!isServiceChargeableForPatient(service.code)" type="checkbox" v-model="service.selected" :value="service.id">
                    <span>{{service.code}}</span>
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
    import { Event } from 'vue-tables-2'

    import SERVICES from '../../../../../../../Sharedvuecomponents/Resources/assets/js/constants/services.types';
    import Modal from '../../../../../../../Sharedvuecomponents/Resources/assets/js/admin/common/modal'
    import Loader from '../../../../../../../Sharedvuecomponents/Resources/assets/js/components/loader'

    export default {
        name: 'chargeable-services-modal',
        props: [ 'services' ],
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
                    okHandler () {
                        console.log(this)
                        if (typeof(this.done) == 'function') {
                            const IDs = self.patientServices
                                            .filter(service => service.selected)
                                            .map(service => service.id)
                            this.done(IDs)
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
             * @param serviceCode
             * @returns {boolean}
             */
            isServiceChargeableForPatient: function (serviceCode) {
                switch (serviceCode) {
                    case SERVICES.CPT_99490:
                        return this.row.hasOver20MinutesCCMTime();
                    case SERVICES.CPT_99484:
                        return this.row.hasOver20MinutesBhiTime();
                    default:
                        return true;
                }
            }
        },
        mounted () {
            Event.$on('modal-chargeable-services:show', (modal) => {
                this.row = (modal || {}).row
                this.info.done = (this.row || {}).onChargeableServicesUpdate
                this.patientServices = this.services.map(service => {
                    service.selected = this.row.chargeable_services.includes(service.id)
                    return service
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