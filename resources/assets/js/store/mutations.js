export const DESTROY_CARE_PERSON = (state, carePerson) => {
    state.patientCareTeam = state.patientCareTeam.filter(function (item) {
        return item.id !== carePerson.id;
    })
}

export const UPDATE_CARE_PERSON = (state, newCarePerson) => {
    let matched = false

    state.patientCareTeam.forEach((carePerson, index) => {
        if (carePerson.id === newCarePerson.id) {
            state.patientCareTeam[index] = newCarePerson;
            matched = true
        }
    })

    if (!matched) {
        state.patientCareTeam.unshift(newCarePerson)
    }
}


export const SET_CARE_TEAM = (state, patientCareTeam) => {
    state.patientCareTeam = patientCareTeam
}

export const CLEAR_CARE_TEAM = (state) => {
    state.patientCareTeam = []
}

export const SET_PATIENT_CARE_PLAN = (state, patientCarePlan) => {
    state.patientCarePlan = patientCarePlan
    state.patientCarePlan.pdfs = _.orderBy(state.patientCarePlan.pdfs, 'label', 'desc');
}

export const CLEAR_PATIENT_CARE_PLAN = (state) => {
    state.patientCarePlan = {}
}

export const ADD_PDF_CARE_PLAN = (state, pdfCareplan) => {
    if (_.isArray(pdfCareplan)) {
        pdfCareplan.forEach((cp) => {
            state.patientCarePlan.pdfs.unshift(cp)
        })
    }
}

export const DELETE_PDF_CARE_PLAN = (state, deletedPdfId) => {
    let removeThis = null;
    for (let i = 0; i < state.patientCarePlan.pdfs.length; i++) {
        if (state.patientCarePlan.pdfs[i].id == deletedPdfId) {
            removeThis = i;
            break;
        }
    }

    if (!_.isNull(removeThis)) {
        state.patientCarePlan.pdfs.splice(removeThis, 1)
    }
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

/**
 * Clear Practice Locations
 * 
 * @param state
 * @constructor
 */
export const CLEAR_PRACTICE_LOCATIONS = (state) => {
    state.practiceLocations = []
}

/**
 * Set Practice Locations 
 * 
 * @param state
 * @param practiceLocations
 * @constructor
 */
export const SET_PRACTICE_LOCATIONS = (state, practiceLocations) => {
    practiceLocations.forEach(loc => {
        state.practiceLocations.push(loc)
    })
}

/**
 * Update Practice Locations
 * 
 * @param state
 * @param location
 * @constructor
 */
export const UPDATE_PRACTICE_LOCATION = (state, location) => {
    state.practiceLocations.forEach((pracLoc, index) => {
        if (pracLoc.id === location.id) {
            state.practiceLocations[index] = location;
        }
    })
}

export const DELETE_PRACTICE_LOCATION = (state, location) => {
    state.practiceLocations.forEach((pracLoc, index) => {
        if (pracLoc.id === location.id) {
            state.practiceLocations.splice(index, 1);
        }
    })
}

export const CLEAR_ERROR = (state, field) => {
    state.errors.clear(field)
}

export const SET_ERRORS = (state, errors) => {
    state.errors.setErrors(errors)
}

/**
 * Clear Practice Users
 *
 * @param state
 * @constructor
 */
export const CLEAR_PRACTICE_USERS = (state) => {
    state.practiceUsers = []
}

/**
 * Set Practice Users
 *
 * @param state
 * @param practiceUsers
 * @constructor
 */
export const SET_PRACTICE_USERS = (state, practiceUsers) => {
    practiceUsers.forEach(user => {
        state.practiceUsers.push(user)
    })
}

/**
 * Update Practice Users
 *
 * @param state
 * @param user
 * @constructor
 */
export const UPDATE_PRACTICE_USER = (state, user) => {
    state.practiceUsers.forEach((pracUser, index) => {
        if (pracUser.id === user.id) {
            state.practiceUsers[index] = user;
        }
    })
}