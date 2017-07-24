export const DESTROY_CARE_PERSON = (state, carePerson) => {
    state.patientCareTeam = state.patientCareTeam.filter(function (item) {
        return item.id !== carePerson.id;
    })
}

export const SET_CARE_TEAM = (state, patientCareTeam) => {
    state.patientCareTeam = patientCareTeam
}

export const CLEAR_CARE_TEAM = () => {
    state.patientCareTeam = {}
}

export const SET_PATIENT_CARE_PLAN = (state, patientCarePlan) => {
    state.patientCarePlan = patientCarePlan
}

export const CLEAR_PATIENT_CARE_PLAN = () => {
    state.patientCarePlan = {}
}

export const ADD_PDF_CARE_PLAN = (state, pdfCareplan) => {
    pdfCareplan.forEach((cp) => {
        state.patientCarePlan.pdfs.push(cp)
    })
}

export const DELETE_PDF_CARE_PLAN = (state, deletedPdfId) => {
    let removeThis = null;
    for (let i = 0; i < state.patientCarePlan.pdfs.length; i++) {
        if (state.patientCarePlan.pdfs[i].id === deletedPdfId) {
            removeThis = i;
            break;
        }
    }
    state.patientCarePlan.pdfs.splice(removeThis, 1)
}

export const LOGIN_USER = (state, currentUser) => {
    state.currentUser = currentUser
}

export const LOGOUT_USER = (state) => {
    state.currentUser = {}
}

export const SET_OPEN_MODAL = (state, openModal) => {
    state.openModal = openModal
}

export const CLEAR_OPEN_MODAL = (state) => {
    state.openModal = {}
}

export const ADD_NOTIFICATION = (state, notification) => {
    state.notifications.push(notification)
}

export const REMOVE_NOTIFICATION = (state, notification) => {
    state.notifications.splice(state.notifications.indexOf(notification), 1)
}

export const CLEAR_PRACTICE_LOCATIONS = (state) => {
    state.practiceLocations = []
}

export const SET_PRACTICE_LOCATIONS = (state, practiceLocations) => {
    practiceLocations.forEach(loc => {
        state.practiceLocations.push(loc)
    })
}
