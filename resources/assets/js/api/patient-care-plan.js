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

    deletePdf (cb, ecb = null, pdfId) {
        window.axios.delete('pdf/' + pdfId).then(
            (resp) => cb(resp.data),
            (resp) => ecb(resp.data)
        );
    },
}