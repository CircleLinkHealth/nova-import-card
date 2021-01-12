import UserRolesHelpers from '../../../mixins/user-roles-helpers.mixin'
export default {
    mixins: [UserRolesHelpers],
    methods: {
        disableEditing() {
            return this.isCallbacksAdmin() || this.isClhCcmAdmin();
        }
    }
}