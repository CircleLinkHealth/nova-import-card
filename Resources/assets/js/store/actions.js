import careTeam from "../../../../../../resources/assets/js/api/care-team";
import practiceLocationsApi from "../../../../../../resources/assets/js/api/practice-location";
import practiceStaffApi from "../../../../../../resources/assets/js/api/practice-staff";
import carePersonApi from "../../../../../../resources/assets/js/api/care-person";
import carePlanApi from "../../../../../../resources/assets/js/api/patient-care-plan";

export const addNotification = ({commit}, notification) => {
    commit('ADD_NOTIFICATION', notification);
}

export const removeNotification = ({commit}, notification) => {
    commit('REMOVE_NOTIFICATION', notification);
}

export const clearNotifications = ({commit}) => {
    commit('CLEAR_NOTIFICATIONS');
}

export const clearOpenModal = ({commit}) => {
    commit('CLEAR_OPEN_MODAL');
}

export const setOpenModal = ({commit}, openModal) => {
    commit('SET_OPEN_MODAL', openModal);
}

export const getPatientCareTeam = ({commit}, patientId) => {
    if (!patientId) {
        return
    }

    careTeam.getPatientCareTeam(careTeam => {
        commit('CLEAR_CARE_TEAM');
        commit('SET_CARE_TEAM', careTeam);
    }, (error) => {
        console.log(error)
    }, patientId)
}

export const updateCarePerson = ({commit}, carePerson, patientId) => {
    commit('UPDATING_CARE_PERSON', carePerson)
    carePersonApi.updateCarePerson((carePerson, oldCarePerson) => {
        commit('UPDATE_CARE_PERSON', carePerson)
        if (oldCarePerson) commit('UPDATE_CARE_PERSON', oldCarePerson)
    }, null, carePerson)
}

export const destroyCarePerson = ({commit}, carePerson) => {
    carePersonApi.destroyCarePerson(carePerson => {
        commit('DESTROY_CARE_PERSON', carePerson);
    }, null, carePerson)
}

/**
 * Get Practice Locations
 *
 * @param commit
 * @param practiceId
 */
export const getPracticeLocations = ({commit}, practiceId) => {
    if (!practiceId) {
        return
    }

    practiceLocationsApi.getPracticeLocations(practice => {
        commit('CLEAR_PRACTICE_LOCATIONS');

        commit('SET_PRACTICE_LOCATIONS', practice);
    }, (error) => {
        console.log(error)
    }, practiceId)
}

/**
 * Update Practice Location
 *
 * @param commit
 * @param location
 */
export const updatePracticeLocation = ({commit}, location) => {
    let practiceId = location.practice_id

    if (!practiceId) {
        console.log('invalid practiceId')
        return
    }

    return practiceLocationsApi.update(location => {
        commit('UPDATE_PRACTICE_LOCATION', location);
    }, errors => {
        commit('SET_ERRORS', errors)
    }, practiceId, location)
}

/**
 * Delete Practice Location
 *
 * @param commit
 * @param location
 */
export const deletePracticeLocation = ({commit}, location) => {
    let practiceId = location.practice_id

    if (!practiceId) {
        console.log('invalid practiceId')
        return
    }

    practiceLocationsApi.delete(location => {
        commit('DELETE_PRACTICE_LOCATION', location);
    }, errors => {
        commit('SET_ERRORS', errors)
    }, practiceId, location)
}

/**
 * Get Practice Staffs
 *
 * @param commit
 * @param practiceId
 */
export const getPracticeStaff = ({commit}, practiceId) => {
    if (!practiceId) {
        return
    }

    practiceStaffApi.index(practice => {
        commit('CLEAR_PRACTICE_STAFF');

        commit('SET_PRACTICE_STAFF', practice);
    }, (error) => {
        console.log(error)
    }, practiceId)
}

/**
 * Update Practice Staff
 *
 * @param commit
 * @param user
 */
export const updatePracticeStaff = ({commit}, user) => {
    let practiceId = user.practice_id

    if (!practiceId) {
        console.log('invalid practiceId')
        return
    }

    commit('UPDATE_PRACTICE_STAFF_WAITING');
    practiceStaffApi.update(user => {
        commit('UPDATE_PRACTICE_STAFF', user);
    }, errors => {
        commit('SET_ERRORS', errors);
    }, practiceId, user)
}

/**
 * Delete Practice Staff
 *
 * @param commit
 * @param user
 */
export const deletePracticeStaff = ({commit}, user) => {
    let practiceId = user.practice_id

    if (!practiceId) {
        console.log('invalid practiceId')
        return
    }

    practiceStaffApi.delete(user => {
        commit('DELETE_PRACTICE_STAFF', user);
    }, errors => {
        commit('SET_ERRORS', errors)
    }, practiceId, user)
}

export const getPatientCarePlan = ({commit}, patientId) => {
    if (!patientId) {
        return
    }

    carePlanApi.getPatientCareplan(carePlan => {
        if (!carePlan) {
            commit('CLEAR_PATIENT_CARE_PLAN');
            return
        }
        commit('SET_PATIENT_CARE_PLAN', carePlan);
    }, error => {
        console.log(error)
    }, patientId)
}

export const destroyPdf = ({commit}, pdfId) => {
    if (!pdfId) {
        return
    }

    carePlanApi.deletePdf(pdf => {
        commit('DELETE_PDF_CARE_PLAN', pdf)
    }, null, pdfId)
}

export const uploadPdfCarePlan = ({commit}, payload) => {
    carePlanApi.uploadPdfCareplan(pdf => {
        commit('ADD_PDF_CARE_PLAN', pdf)
    }, error => {
        console.log(error)
    }, payload)
}

export const clearErrors = ({commit}, field) => {
    commit('CLEAR_ERROR', field)
}
