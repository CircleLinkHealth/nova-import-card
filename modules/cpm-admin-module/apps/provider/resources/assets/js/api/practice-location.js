import Vue from 'vue'

export default {
    getPracticeLocations(cb, ecb = null, practiceId) {
        if (!practiceId) {
            return;
        }

        window.axios.get('practice/' + practiceId + '/locations').then(
            (resp) => cb(resp.data),
            (resp) => {
                console.error(resp);
                if (typeof ecb === 'function') {
                    ecb(resp.data);
                }
            }
        );
    },

    update(cb, ecb = null, practiceId, location) {
        if (!practiceId) {
            return;
        }

        return window.axios.patch('practice/' + practiceId + '/locations/' + location.id, location).then(
            (resp) => {
                cb(resp.data)
            },
            (error) => {
                console.log(error);
                if (typeof ecb === 'function') {
                    ecb(error.response.data.errors);
                }
            }
        );
    },

    delete(cb, ecb = null, practiceId, location) {
        if (!practiceId) {
            return;
        }

        window.axios.delete('practice/' + practiceId + '/locations/' + location.id).then(
            (resp) => cb(resp.data),
            (error) => {
                console.log(error);
                if (typeof ecb === 'function') {
                    ecb(error.response.data.errors);
                }
            }
        );
    },
}