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
                <select class="form-control" v-model="props.info.code" @change="props.info.changeProblemName">
                  <option v-for="(problem, index) in props.info.problems" :key="index" :value="problem.code">{{problem.name}}</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-12">
                ICD-10 Code
              </div>
              <div class="col-sm-12">
                <input class="form-control" type="text" v-model="props.info.code" placeholder="Code" />
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

                        if (this.done && typeof(this.done) === 'function') {
                          this.done(this)
                        }
                    },
                    changeProblemName() {
                      this.name = (this.problems.find(problem => problem.code === this.code) || {}).name
                    }
                }
            }
        },
        methods: {
          
        },
        mounted() {
          Event.$on('modal-patient-problem:show', (patientProblem, type, done) => {
            this.patientProblemModalInfo.problems = patientProblem.problems
            this.patientProblemModalInfo.name = (type === 1) ? patientProblem['Problem 1'] : patientProblem['Problem 1']
            this.patientProblemModalInfo.code = (type === 1) ? patientProblem['Problem 1 Code'] : patientProblem['Problem 1 Code']
            if (done && typeof(done) == 'function') this.patientProblemModalInfo.done = done.bind(this.patientProblemModalInfo)
            this.patientProblemModalInfo.changeProblemName = this.patientProblemModalInfo.changeProblemName.bind(this.patientProblemModalInfo)
          })
        }
    }
</script>

<style>
    .modal-patient-problem .modal-container {
        width: 420px;
    }
</style>