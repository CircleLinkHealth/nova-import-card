export default {
    props: {
        authRole: {
            type: String,
            required: true,
        }
    },
    methods: {
        isAdmin() {
            return this.authRole === 'administrator';
        },
        isSoftwareOnly() {
            return this.authRole === 'software-only';
        },
        isCallbacksAdmin() {
            return this.authRole === 'callbacks-admin';
        },
        isClhCcmAdmin() {
            return this.authRole === 'clh-ccm-admin';
        },
    }
}