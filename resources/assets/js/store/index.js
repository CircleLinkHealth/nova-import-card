import Vue from "vue";
import Vuex from "vuex";
import * as actions from './actions'
import * as getters from './getters'

Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'
Vue.config.debug = debug

const state = {
    currentUser: {}, //not implemented yet
    debug: debug,
    form: {
        show: false,
        busy: false,
        success: false,
        errors: {}
    }
}

const mutations = {
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