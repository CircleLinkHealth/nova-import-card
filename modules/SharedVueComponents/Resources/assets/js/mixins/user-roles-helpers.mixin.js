export default {
    props: {
        authRole: {
            type: String,
            required: false,
            default: ''
        },
        authRoles: {
            type: Array,
            required: false,
            default: []
        }
    },
    methods: {
        isAdmin() {
            const role = 'administrator';
            return this.hasRole(role);
        },
        isSoftwareOnly() {
            const role = 'software-only';
            return this.hasRole(role);
        },
        isCallbacksAdmin() {
            const role = 'callbacks-admin';
            return this.hasRole(role);
        },
        isClhCcmAdmin() {
            const role = 'clh-ccm-admin';
            return this.hasRole(role);
        },
        hasRole(role) {
            return this.authRole === role || this.authRoles.indexOf(role) > -1;
        }
    }
}
