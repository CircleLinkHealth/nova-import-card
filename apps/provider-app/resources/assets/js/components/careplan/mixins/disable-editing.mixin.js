import UserRolesHelpers from '../../../../../../CircleLinkHealth/SharedVueComponents/Resources/assets/js/mixins/user-roles-helpers.mixin'
export default {
    mixins: [UserRolesHelpers],
    methods: {
        disableEditing() {
            return this.isCallbacksAdmin() || this.isClhCcmAdmin();
        }
    }
}