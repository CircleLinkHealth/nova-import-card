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
            (resp) => {
                const createNewPerson = (newCarePerson) => {
                    if (newCarePerson.user) newCarePerson.user = Object.assign(newCarePerson.user, carePerson.user)
                    if (newCarePerson.is_billing_provider && carePerson.is_billing_provider) {
                        newCarePerson.formatted_type = 'External'
                        newCarePerson.is_billing_provider = false
                    }
                    else {
                        if (!carePerson.is_billing_provider) {
                            newCarePerson.formatted_type = 'Provider (Internal)'
                        }
                        else {
                            newCarePerson.is_billing_provider = carePerson.is_billing_provider
                            newCarePerson.formatted_type = carePerson.formatted_type
                        }
                    }
                    return newCarePerson
                }
                const modOldBillingProvider = (oldCarePerson) => {
                    if (oldCarePerson) {
                        if (oldCarePerson.type == 'external') {
                            oldCarePerson.formatted_type = 'Provider (External)'
                        }
                    }
                    return oldCarePerson
                }
                cb(createNewPerson(resp.data.carePerson), modOldBillingProvider(resp.data.oldBillingProvider))
            },
            (resp) => {
                if (typeof(ecb) == 'function') {
                    ecb(resp.data)
                }
            }
        );
    },
}