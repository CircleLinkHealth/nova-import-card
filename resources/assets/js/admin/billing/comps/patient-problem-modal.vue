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
                <select class="form-control" v-model="props.info.id" @change="props.info.changeProblemName">
                  <option value="New">New</option>
                  <option v-for="(problem, index) in props.info.problems" :key="index" :value="problem.id">{{problem.name}}</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            <div class="row form-group" v-if="props.info.id == 'Other'">
              <div class="col-sm-12">
                Choose Problem
              </div>
              <div class="col-sm-12">
                <select class="form-control" v-model="props.info.cpm_id" @change="props.info.changeCpmProblemName">
                  <option v-for="(problem, index) in props.info.cpmProblems" :key="index" :value="problem.id">{{problem.name}}</option>
                </select>
              </div>
            </div>
            <div class="row form-group" v-if="props.info.cpm_id">
              <div class="col-sm-12">
                Problem Name
              </div>
              <div class="col-sm-12">
                <input class="form-control" type="text" v-model="props.info.name" placeholder="Name" />
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
            cpmProblems: Array
        },
        components: {
            'modal': Modal
        },
        data() {
            const self = this
            return {
                patientProblemModalInfo: {
                    okHandler() {
                        console.log("okay clicked")
                        Event.$emit("modal-patient-problem:hide")
                        this.cpm_id = 0
                        if (this.done && typeof(this.done) === 'function') {
                          console.log('ok-handler', this)
                          this.done(this)
                        }
                    },
                    changeProblemName(e) {
                      const problem = (this.problems.find(problem => problem.id === this.id) || {})
                      Object.assign(this, problem)
                      if (this.id === 'New' || this.id === 'Other') {
                        this.code = ''
                        this.name = ''
                      }
                      self.$forceUpdate()
                    },
                    changeCpmProblemName(e) {
                      const cpmProblem = (this.cpmProblems.find(problem => problem.id == e.target.value) || {})
                      this.name = cpmProblem.name
                      this.code = cpmProblem.code
                    },
                    cpmProblems: this.cpmProblems,
                    cpm_id: null
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
            this.patientProblemModalInfo.id = (patientProblem.problems.find(problem => problem.code === this.patientProblemModalInfo.code) || {}).id
            console.log(this.patientProblemModalInfo)
            if (done && typeof(done) == 'function') this.patientProblemModalInfo.done = done.bind(this.patientProblemModalInfo)
            this.patientProblemModalInfo.changeProblemName = this.patientProblemModalInfo.changeProblemName.bind(this.patientProblemModalInfo)
            this.patientProblemModalInfo.changeCpmProblemName = this.patientProblemModalInfo.changeCpmProblemName.bind(this.patientProblemModalInfo)
          })
        }
    }
</script>

<style>
    .modal-patient-problem .modal-container {
        width: 420px;
    }
</style>