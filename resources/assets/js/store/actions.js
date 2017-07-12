export const cancelForm = (context) => {
    context.commit('CLEAR_FORM');
}

export const showForm = (context) => {
    context.commit('SET_FORM_SHOW', true);
}
