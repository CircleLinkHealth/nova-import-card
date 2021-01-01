export default {
    destroyCarePerson(cb, ecb = () => ({}), carePerson) {
        window.axios.delete('user/' + carePerson.user_id + '/care-team/' + carePerson.id).then(
            (resp) => cb(carePerson),
            (resp) => {
                console.error(resp);
                if (typeof(ecb) === 'function') {
                    ecb(resp.data)
                }
            }
        );
    },
    updateCarePerson(cb, ecb = () => ({}), carePerson) {
        window.axios.patch('user/' + carePerson.user_id + '/care-team/' + carePerson.id, carePerson).then(
            (resp) => {
                const createNewPerson = (newCarePerson) => {
                    if (newCarePerson.user) newCarePerson.user = Object.assign(newCarePerson.user, carePerson.user)
                    return newCarePerson
                };
                const modOldBillingProvider = (oldCarePerson) => {
                    if (oldCarePerson) {
                        if (oldCarePerson.type === 'external') {
                            oldCarePerson.formatted_type = 'Provider (External)'
                        }
                        else if (oldCarePerson.type === 'internal') {
                            oldCarePerson.formatted_type = 'Provider (Internal)'
                        }
                    }
                    return oldCarePerson
                };
                cb(createNewPerson(resp.data.carePerson), modOldBillingProvider(resp.data.oldBillingProvider))
            },
            (resp) => {
                console.error(resp);
                if (typeof(ecb) === 'function') {
                    ecb(resp.data)
                }
            }
        );
    },
}