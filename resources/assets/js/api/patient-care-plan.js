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

    uploadPdfCareplan (cb, ecb = null, formData) {
        // formdata needs to contain
        // formData.set('files[' + i + ']', this.files[i].file)
        // formData.set('carePlanId', this.patientCarePlan.id)
        window.axios.post('care-plans/' + formData.get('carePlanId') + '/pdfs', formData).then(
            (resp) => cb(resp.data),
            (resp) => ecb(resp.data)
        );
    }
}