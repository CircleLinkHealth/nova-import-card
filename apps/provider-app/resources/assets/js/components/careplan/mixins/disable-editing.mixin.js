import UserRolesHelpers from '../../../../../../vendor/circlelinkhealth/sharedvuecomponents-module/Resources/assets/js/mixins/user-roles-helpers.mixin'
export default {
    mixins: [UserRolesHelpers],
    methods: {
        disableEditing() {
            return this.isCallbacksAdmin() || this.isClhCcmAdmin();
        }
    }
}