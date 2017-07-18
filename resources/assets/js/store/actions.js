import userProfile from "../api/user-profile";
import careTeam from "../api/care-team";
import practiceLocationsApi from "../api/practice-location";
import carePersonApi from "../api/care-person";

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
        if (!careTeam) {
            commit('CLEAR_CARE_TEAM');
            return
        }
        commit('SET_CARE_TEAM', careTeam);
    }, null, patientId)
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
        if (!practice) {
            commit('CLEAR_PRACTICE_LOCATIONS');
            return
        }
        commit('SET_PRACTICE_LOCATIONS', practice);
    }, (error) => {
        console.log(error)
    }, practiceId)
}

