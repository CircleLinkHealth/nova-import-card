export default {
    getPatientCareplan(cb, ecb = null, patientId) {
        if (!patientId) {
            return;
        }

        window.axios.get('user/' + patientId + '/care-plan').then(
            (resp) => cb(resp.data),
            (resp) => {
                console.error(resp);
                if (typeof ecb === 'function') {
                    ecb(resp.data);
                }
            }
        );
    },

    deletePdf(cb, ecb = null, pdfId) {
        window.axios.delete('pdf/' + pdfId).then(
            (resp) => cb(resp.data),
            (resp) => {
                console.error(resp);
                if (typeof ecb === 'function') {
                    ecb(resp.data);
                }
            }
        );
    },

    uploadPdfCareplan(cb, ecb = null, payload) {
        window.axios.post('care-plans/' + payload.carePlanId + '/pdfs', payload.formData).then(
            (resp) => cb(resp.data),
            (resp) => {
                console.error(resp);
                if (typeof ecb === 'function') {
                    ecb(resp.data);
                }
            }
        );
    }
}