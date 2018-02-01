export default {
    destroyCarePerson (cb, ecb = () => ({}), carePerson) {
        window.axios.delete('user/' + carePerson.user_id + '/care-team/' + carePerson.id).then(
            (resp) => cb(carePerson),
            (resp) => {
                if (typeof(ecb) == 'function') {
                    ecb(resp.data)
                }
            }
        );
    },
    updateCarePerson (cb, ecb = () => ({}), carePerson) {
        window.axios.patch('user/' + carePerson.user_id + '/care-team/' + carePerson.id, carePerson).then(
            (resp) => cb(carePerson),
            (resp) => {
                if (typeof(ecb) == 'function') {
                    ecb(resp.data)
                }
            }
        );
    },
}