export default {
    getPatientCareplan (cb, ecb = null, patientId) {
        if (!patientId) {
            return;
        }

        window.axios.get('user/' + patientId + '/care-plan').then(
            (resp) => cb(resp.data),
            (resp) => ecb(resp.data)
        );
    },
}