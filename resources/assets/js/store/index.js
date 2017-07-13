import Vue from "vue";
import Vuex from "vuex";
import * as actions from "./actions";
import * as getters from "./getters";

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'
Vue.config.debug = debug

const state = {
    patientCareTeam: [],
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
    form: {
        show: false,
        busy: false,
        success: false,
        errors: {}
    }
}

const mutations = {
    DESTROY_CARE_PERSON(state, carePerson) {
        state.patientCareTeam = state.patientCareTeam.filter(function (item) {
            return item.id !== carePerson.id;
        })
    },
    SET_CARE_TEAM(state, patientCareTeam) {
        state.patientCareTeam = patientCareTeam
    },
    CLEAR_CARE_TEAM(patientCareTeam) {
        state.patientCareTeam = {}
    },
    LOGIN_USER (state, currentUser) {
        state.currentUser = currentUser
    },
    LOGOUT_USER (state) {
        state.currentUser = {}
    },
    SET_ERRORS (state, errors) {
        state.form.errors = errors
    },
    SET_FORM_BUSY (state, value) {
        state.form.busy = value
    },
    SET_FORM_SHOW (state, value) {
        state.form.show = value
    },
    CLEAR_FORM (state, value) {
        state.form.busy = false
        state.form.show = false
        state.form.errors = {}
    }
}


export default new Vuex.Store({
    state,
    getters,
    actions,
    mutations,
    strict: debug
})