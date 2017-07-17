import Vue from 'vue'

export default {
    getCurrentUser (cb, ecb = null) {
        window.axios.get('profiles').then(
            (resp) => cb(resp.data.user),
            (resp) => ecb(resp.data)
        );
    },
}