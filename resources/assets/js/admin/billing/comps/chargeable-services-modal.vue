<template>
    <modal name="chargeable-services" ref="modal" :info="info" :no-footer="true" class-name="modal-patient-problem">
      <template slot-scope="props">
        <div class="row">
          <div class="col-sm-12">
            <div class="row form-group">
              <div class="col-sm-12 form-control margin-5" v-for="service in patientServices" :key="service.id">
                <label>
                    <input type="checkbox" v-model="service.selected" :value="service.id"> {{service.code}}
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
    import Modal from '../../common/modal'
    import Loader from '../../../components/loader'

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

    .margin-5 {
        margin: 5px;
    }
</style>