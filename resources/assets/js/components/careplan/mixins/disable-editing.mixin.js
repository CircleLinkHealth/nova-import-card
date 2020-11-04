import UserRolesHelpers from '../../../mixins/user-roles-helpers.mixin'
export default {
    mixins: [UserRolesHelpers],
    computed: {
        disableEditing() {
            return this.isCallbacksAdmin || this.isClhCcmAdmin;
        }
    }
}