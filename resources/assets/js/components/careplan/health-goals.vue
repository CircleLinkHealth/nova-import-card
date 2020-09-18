<template>
    <div class="patient-info__subareas">
        <div class="row">
            <div class="col-xs-12">
                <h2 class="patient-summary__subtitles patient-summary--careplan-background">Your Health Goals
                    <span class="btn btn-primary glyphicon glyphicon-edit" @click="showModal" aria-hidden="true"></span>
                </h2>
            </div>
        </div>
        <div class="row gutter">
            <div class="col-xs-12">
                <div class="row top-10">
                    <div :class="{ 'col-sm-12': !loaders.editNote && !loaders.getNote, 'col-sm-11': loaders.editNote }">
                        <form @submit="editNote">
                            <textarea class="form-control free-note" v-model="note.body" placeholder="Enter Note and press ENTER" @change="editNote"  @keydown.enter.exact.prevent @keyup.enter.exact="editNote"></textarea>
                        </form>
                    </div>
                    <div class="col-sm-1" v-if="loaders.editNote || loaders.getNote">
                        <loader></loader>
                    </div>
                </div>

                <div class="text-center" v-if="goals.length === 0 && !note.body">No Health Goals at this time</div>
                
                <ul class="subareas__list top-10" v-if="goals && goals.length > 0">
                    <li class='subareas__item subareas__item--wide row top-10' v-for="goal in goalsForListing()" :key="goal.id">
                        <div class="col-xs-5 print-row text-bold">{{goal.info.verb}} {{goal.name}}</div>
                        <div class="col-xs-4 print-row text-bold">{{(goal.name == 'Weight' && !Number(goal.info.target)) ? 'to' : ((goal.info.verb === 'Regulate') ? 'keep under' :  'to') }} {{goal.end() || 'N/A'}} {{goal.unit}}</div>
                        <div class="col-xs-3 print-row">
                            from {{goal.start() || 'N/A'}} {{goal.unit}}</div>
                    </li>
                </ul>
            </div>
        </div>
        <health-goals-modal ref="healthGoalsModal" :patient-id="patientId" :goals="goals"></health-goals-modal>
    </div>
</template>

<script>
    import { rootUrl } from '../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/app.config'
    import { Event } from 'vue-tables-2'
    import HealthGoalsModal from './modals/health-goals.modal'
    import NoteTypes from '../../constants/note.types'
    import CareplanMixin from './mixins/careplan.mixin'
    import transformHealthGoal from './utils/health-goal-transform'

    export default {
        name: 'care-areas',
        props: [
            'patient-id'
        ],
        components: {
            'health-goals-modal': HealthGoalsModal
        },
        mixins: [ CareplanMixin ],
        computed: {
            
        },
        data() {
            return {
                baseGoals: [],
                goals: [],
                note: {
                    id: null,
                    body: null,
                    type: NoteTypes.Biometrics
                },
                loaders: {
                    editNote: null,
                    getNote: null
                }
            }
        },
        methods: {
            goalsForListing () {
                return this.goals.filter(goal => goal.enabled)
            },
            setupGoal(goal) {
                return transformHealthGoal(goal)
            },
            getBaseGoals() {
                return this.axios.get(rootUrl(`api/biometrics`)).then(response => {
                    this.baseGoals = response.data
                    // console.log('health-goals:get-base-goals', this.baseGoals)
                    return this.baseGoals
                }).catch(err => {
                    console.error('health-goals:get-base-goals', err)
                })
            },
            getGoals() {
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/biometrics`)).then(response => {
                    const goals = response.data.map(this.setupGoal)
                    // console.log('health-goals:get-goals', goals)
                    return goals
                }).catch(err => {
                    console.error('health-goals:get-goals', err)
                })
            },
            showModal() {
                Event.$emit('modal-health-goals:show')
            },
            getNote() {
                this.loaders.getNote = true
                return this.axios.get(rootUrl(`api/patients/${this.patientId}/notes?type=${NoteTypes.Biometrics}`)).then(response => {
                    this.note = ((response.data || {}).data || [])[0] || this.note
                    // console.log('health-goals:notes', this.note)
                    this.loaders.getNote = false
                }).catch(err => {
                    console.error('health-goals:notes', err)
                    this.loaders.getNote = false
                })
            },
            editNote(e) {
                e.preventDefault()
                this.loaders.editNote = true
                let $promise = null
                if (this.note.id) {
                    $promise = this.axios.put(rootUrl(`api/patients/${this.patientId}/notes/${this.note.id}`), this.note)
                }
                else {
                    $promise = this.axios.post(rootUrl(`api/patients/${this.patientId}/notes`), this.note)
                }
                return $promise.then(response => {
                    console.log('health-goals:note-add', response.data)
                    Event.$emit('health-goals:note-add', response.data)
                    if (response.data) this.note = response.data
                    this.loaders.editNote = false
                }).catch(err => {
                    console.error('health-goals:note-add', err)
                    this.loaders.editNote = false
                })
            }
        },
        mounted() {
            const goals = this.careplan().healthGoals
            const textarea = this.$el.querySelector('textarea')

            const autoGrow = function () {
                this.style.height = '50px'
                this.style.height = this.scrollHeight + 'px'
            }

            textarea.addEventListener('input', autoGrow)
            
            this.note = this.careplan().healthGoalNote || this.note
            // console.log('patient-note', this.note)
            setTimeout(() => autoGrow.call(textarea), 500)

            this.baseGoals = this.careplan().baseHealthGoals
            this.goals = this.baseGoals.map(baseGoal => {
                        return this.setupGoal(goals.find(g => g.id === baseGoal.id) || baseGoal)
                    })

            Event.$on('problems:updated', this.getGoals.bind(this))
            Event.$on('health-goals:goals', (goals) => {
                this.goals = goals
            })

            Event.$on('health-goals:add', (id, info) => {
                const index = this.goals.findIndex(g => g.id == id)
                if (index >= 0) {
                    Object.assign(this.goals[index], this.setupGoal({ info, enabled: true, name: this.goals[index].name }))
                    this.$forceUpdate()
                }
            })

            Event.$on('health-goals:remove', (id) => {
                const index = this.goals.findIndex(g => g.id == id)
                if (index >= 0) {
                    this.goals[index] = this.setupGoal(this.goals[index])
                    this.$forceUpdate()
                }
            })
        }
    }
</script>

<style>
    li.top-20 {
        margin-top: 20px;
    }

    .free-note {
        border: none;
        font-size: 26px;
        color: black;
        background-color: transparent;
        box-shadow: none;
    }
</style>