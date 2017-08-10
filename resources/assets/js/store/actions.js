import userProfile from "../api/user-profile";
import careTeam from "../api/care-team";
import practiceLocationsApi from "../api/practice-location";
import carePersonApi from "../api/care-person";
import carePlanApi from "../api/patient-care-plan";

export const addNotification = ({commit}, notification) => {
    commit('ADD_NOTIFICATION', notification);
}

export const removeNotification = ({commit}, notification) => {
    commit('REMOVE_NOTIFICATION', notification);
}

export const clearOpenModal = ({commit}) => {
    commit('CLEAR_OPEN_MODAL');
}

export const setOpenModal = ({commit}, openModal) => {
    commit('SET_OPEN_MODAL', openModal);
}

export const getCurrentUser = ({commit}) => {
    userProfile.getCurrentUser(user => {
        if (!user) {
            commit('LOGOUT_USER');
            return
        }
        commit('LOGIN_USER', user);
    })
}

export const getPatientCareTeam = ({commit}, patientId) => {
    if (!patientId) {
        return
    }

    careTeam.getPatientCareTeam(careTeam => {
        commit('CLEAR_CARE_TEAM');
        commit('SET_CARE_TEAM', careTeam);
    }, null, patientId)
}

export const updateCarePerson = ({commit}, carePerson) => {
    carePersonApi.updateCarePerson(carePerson => {
        commit('UPDATE_CARE_PERSON', carePerson);
    }, null, carePerson)
}

export const destroyCarePerson = ({commit}, carePerson) => {
    carePersonApi.destroyCarePerson(carePerson => {
        commit('DESTROY_CARE_PERSON', carePerson);
    }, null, carePerson)
}

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

export const updatePracticeLocation = ({commit}, location) => {
    let practiceId = location.practice_id

    if (!practiceId) {
        console.log('invalid practiceId')
        return
    }

    practiceLocationsApi.update(location => {
        commit('UPDATE_PRACTICE_LOCATION', location);
    }, errors => {
        commit('SET_ERRORS', errors)
    }, practiceId, location)
}

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

export const uploadPdfCarePlan = ({commit}, formData) => {
    if (!formData) {
        return
    }

    carePlanApi.uploadPdfCareplan(pdf => {
        commit('ADD_PDF_CARE_PLAN', pdf)
    }, error => {
        console.log(error)
    }, formData)
}

export const clearErrors = ({commit}, field) => {
    commit('CLEAR_ERROR', field)
}
