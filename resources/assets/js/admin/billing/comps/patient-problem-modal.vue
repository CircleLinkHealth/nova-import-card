<template>
    <modal name="patient-problem" :info="patientProblemModalInfo" :no-footer="true" class-name="modal-patient-problem">
      <template slot="title" scope="props"><div>Select Eligible Problem for {{props.Patient}}</div></template>
      <template scope="props">
        <div class="row">
          <div class="col-sm-12">
            <div class="row form-group">
              <div class="col-sm-12">
                Eligible Problems
              </div>
              <div class="col-sm-12">
                <select class="form-control">
                  <option v-for="(problem, index) in problems" :key="index" :value="problem.code">{{problem.name}}</option>
                  <option value="">Other</option>
                </select>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-12">
                Eligible Problems
              </div>
              <div class="col-sm-12">
                <select class="form-control">
                  <option v-for="(problem, index) in problems" :key="index" :value="problem.code">{{problem.name}}</option>
                  <option value="">Other</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </template>
    </modal>
</template>

<script>
    import { Event } from 'vue-tables-2'
    import Modal from '../../common/modal'
    export default {
        name: 'patient-problem-modal',
        props: {
            problems: Array
        },
        components: {
            'modal': Modal
        },
        data() {
            return {
                patientProblemModalInfo: {
                    okHandler() {
                        console.log("okay clicked")
                        Event.$emit("modal-patient-problem:hide")
                    }
                }
            }
        }
    }
</script>

<style>
    .modal-patient-problem .modal-container {
        width: 420px;
    }
</style>