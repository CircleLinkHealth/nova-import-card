import Vue from "vue";
import Vuex from "vuex";
import * as actions from "./actions";
import * as getters from "./getters";
import * as mutations from "./mutations";

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

export default new Vuex.Store({
    state,
    getters,
    actions,
    mutations,
    strict: debug
})