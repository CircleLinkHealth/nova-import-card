import Vue from 'vue'

export default {
    getPatientCareTeam (cb, ecb = null, patientId) {
        if (!patientId) {
            return;
        }

        window.axios.get('user/' + patientId + '/care-team').then(
            (resp) => cb(resp.data),
            (resp) => {
                console.error(resp);
                if (typeof ecb === 'function'){
                    ecb(resp.data);
                }
            }
        );
    },
}