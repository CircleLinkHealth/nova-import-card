import userProfile from "../api/user-profile";
import careTeam from "../api/care-team";
import carePersonApi from "../api/care-person";

export const cancelForm = ({commit}) => {
    commit('CLEAR_FORM');
}

export const showForm = ({commit}) => {
    commit('SET_FORM_SHOW', true);
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