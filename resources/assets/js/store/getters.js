export const patientCareTeam = state => {
    return state.patientCareTeam
}

export const patientCarePlan = state => {
    state.patientCarePlan.pdfs = _.orderBy(state.patientCarePlan.pdfs, 'label', 'desc');
    return state.patientCarePlan
}

export const currentUser = state => {
    return state.currentUser
}

export const openModal = state => {
    return state.openModal
}

export const notifications = state => {
    return state.notifications
}

export const practiceLocations = state => {
    return state.practiceLocations
}

export const errors = state => {
    return state.errors
}
