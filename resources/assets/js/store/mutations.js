export const DESTROY_CARE_PERSON = (state, carePerson) => {
    state.patientCareTeam = state.patientCareTeam.filter(function (item) {
        return item.id !== carePerson.id;
    })
};

export const UPDATING_CARE_PERSON = (state, newCarePerson) => {
    state.patientCareTeamIsUpdating = true;
}

export const UPDATE_CARE_PERSON = (state, newCarePerson) => {

    state.patientCareTeamIsUpdating = false;

    let exists = false

    const team = (state.patientCareTeam || [])

    if (newCarePerson) {
        state.patientCareTeam = team.map((person, index) => {
            if (newCarePerson.id === person.id) {
                newCarePerson.user = person.user
                return Object.assign(person, newCarePerson)
            }
            else {
                return person
            }
        })
    }

    

    // state.patientCareTeam.forEach((carePerson, index) => {
    //     if (carePerson.id === newCarePerson.id) {
    //         Vue.set(state.patientCareTeam, index, newCarePerson)
    //         console.log('mutation:care-person', newCarePerson)
    //         exists = true
    //     }

    //     if (newCarePerson.is_billing_provider && carePerson.is_billing_provider) {
    //         Vue.set(state.patientCareTeam[index], 'is_billing_provider', false)
    //         Vue.set(state.patientCareTeam[index], 'formatted_type', 'External')
    //     }
    // })

    if (!team.find(person => person.id === newCarePerson.id)) {
        state.patientCareTeam.push(newCarePerson)
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

export const CLEAR_NOTIFICATIONS = (state) => {
    state.notifications = [];
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
            Vue.set(state.practiceLocations, index, location)
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
    state.practiceStaffIsUpdating = false;
    state.errors.setErrors(errors)
}

/**
 * Clear Practice Staff
 *
 * @param state
 * @constructor
 */
export const CLEAR_PRACTICE_STAFF = (state) => {
    state.practiceStaff = []
}

/**
 * Set Practice Staff
 *
 * @param state
 * @param practiceStaff
 * @constructor
 */
export const SET_PRACTICE_STAFF = (state, practiceStaff) => {
    practiceStaff.forEach(user => {
        state.practiceStaff.push(user)
    })
}

/**
 * Update Practice Staff Member
 *
 * @param state
 * @param user
 * @constructor
 */
export const UPDATE_PRACTICE_STAFF = (state, user) => {
    state.practiceStaffIsUpdating = false;
    state.practiceStaff.forEach((pracUser, index) => {
        if (pracUser.id === user.id) {
            Vue.set(state.practiceStaff, index, user)
        }
    })
}

export const DELETE_PRACTICE_STAFF = (state, user) => {
    state.practiceStaffIsUpdating = false;
    state.practiceStaff.forEach((pracUser, index) => {
        if (pracUser.id === user.id) {
            state.practiceStaff.splice(index, 1);
        }
    })
}

export const UPDATE_PRACTICE_STAFF_WAITING = (state) => {
    state.practiceStaffIsUpdating = true;
}