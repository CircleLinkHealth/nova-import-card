import Vue from 'vue'

export default {
    index (cb, ecb = null, practiceId) {
        if (!practiceId) {
            return;
        }

        window.axios.get('practice/' + practiceId + '/users').then(
            (resp) => cb(resp.data),
            (resp) => ecb(resp.data)
        );
    },

    update (cb, ecb = null, practiceId, user) {
        if (!practiceId) {
            return;
        }

        window.axios.patch('practice/' + practiceId + '/users/' + user.id, user).then(
            (resp) => cb(resp.data),
            (error) => ecb(error.response.data)
        );
    },

    delete (cb, ecb = null, practiceId, user) {
        if (!practiceId) {
            return;
        }

        window.axios.delete('practice/' + practiceId + '/users/' + user.id).then(
            (resp) => cb(resp.data),
            (error) => ecb(error.response.data)
        );
    },
}