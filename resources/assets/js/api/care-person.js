export default {
    destroyCarePerson (cb, ecb = null, carePerson) {
        window.axios.delete('user/' + carePerson.user_id + '/care-team/' + carePerson.id).then(
            (resp) => cb(carePerson),
            (resp) => ecb(resp.data)
        );
    },
    updateCarePerson (cb, ecb = null, carePerson) {
        window.axios.patch('user/' + carePerson.user_id + '/care-team/' + carePerson.id, carePerson).then(
            (resp) => cb(carePerson),
            (resp) => ecb(resp.data)
        );
    },
}