export const DESTROY_CARE_PERSON = (state, carePerson) => {
    state.patientCareTeam = state.patientCareTeam.filter(function (item) {
        return item.id !== carePerson.id;
    })
}

export const SET_CARE_TEAM = (state, patientCareTeam) => {
    state.patientCareTeam = patientCareTeam
}

export const CLEAR_CARE_TEAM = (patientCareTeam) => {
    state.patientCareTeam = {}
}

export const LOGIN_USER = (state, currentUser) => {
    state.currentUser = currentUser
}

export const LOGOUT_USER = (state) => {
    state.currentUser = {}
}

export const SET_ERRORS = (state, errors) => {
    state.form.errors = errors
}

export const SET_FORM_BUSY = (state, value) => {
    state.form.busy = value
}

export const SET_FORM_SHOW = (state, value) => {
    state.form.show = value
}

export const CLEAR_FORM = (state, value) => {
    state.form.busy = false
    state.form.show = false
    state.form.errors = {}
}