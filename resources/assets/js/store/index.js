import Vue from "vue";
import Vuex from "vuex";
import * as actions from "./actions";
import * as getters from "./getters";
import * as mutations from "./mutations";
import Errors from '../components/src/Errors'

Vue.use(Vuex)

// const debug = process.env.NODE_ENV !== 'production'
const debug = true
Vue.config.debug = true

const state = {
    errors: new Errors(),
    currentUser: {
        email: '',
        first_name: '',
        id: '',
        last_name: '',
        program_id: '',
        role: {
            name: '',
            id: ''
        },
        username: '',
    },
    debug: debug,
    notifications: [],
    openModal: {
        name: null,
        props: {}
    },
    patientCareTeamIsUpdating: false,
    patientCareTeam: [],
    patientCarePlan: {
        pdfs: []
    },
    practiceLocations: [],
    practiceStaff: [],
}

export default new Vuex.Store({
    state,
    getters,
    actions,
    mutations,
    strict: debug
})