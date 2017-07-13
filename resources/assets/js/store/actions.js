import userProfile from '../api/user-profile'

export const cancelForm = ({ commit }) => {
    commit('CLEAR_FORM');
}

export const showForm = ({ commit }) => {
    commit('SET_FORM_SHOW', true);
}

export const getCurrentUser = ({ commit }) => {
    userProfile.getCurrentUser(user => {
        if (!user) {
            commit('LOGOUT_USER');
            return
        }
        commit('LOGIN_USER', user);
    })
}