import Vue from "vue";
import Vuex from "vuex";
import * as actions from "./actions";
import * as getters from "./getters";
import * as mutations from "./mutations";

//temporarily here till I figure out how to export a js class
class Errors {
    constructor() {
        this.errors = {}
    }

    get(field) {
        if (this.errors[field]) {
            return this.errors[field][0]
        }
    }

    setErrors(errors) {
        this.errors = errors
    }

    clear(field) {
        delete this.errors[field]
    }
}


Vue.use(Vuex)

const debug = process.env.NODE_ENV !== 'production'
Vue.config.debug = debug

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
    patientCareTeam: [],
    patientCarePlan: {
        pdfs: []
    },
    practiceLocations: [],
}

export default new Vuex.Store({
    state,
    getters,
    actions,
    mutations,
    strict: debug
})