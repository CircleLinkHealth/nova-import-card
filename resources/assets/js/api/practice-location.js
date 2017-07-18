import Vue from 'vue'

export default {
    getPracticeLocations (cb, ecb = null, practiceId) {
        if (!practiceId) {
            return;
        }

        window.axios.get('practice/' + practiceId + '/locations').then(
            (resp) => cb(resp.data),
            (resp) => ecb(resp.data)
        );
    },
}