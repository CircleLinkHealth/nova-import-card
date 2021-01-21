require('../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/bootstrap');
require('../../../../public/js/materialize.min');
require('select2');

window.Vue = require('vue');

Vue.component('material-select', require('../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/src/material-select.vue'));
Vue.component('select2', require('../../../../../../CircleLinkHealth/Sharedvuecomponents/Resources/assets/js/components/src/select2.vue'));

/**
 *
 * CREATE STAFF VUE INSTANCE
 *
 */
var createStaffVM = new Vue({
    el: '#create-staff-component',

    data: function () {
        return {
            locations: [],
            locationIds: [],
            newUsers: [],
            roles: [],
            rolesMap: [],
            deleteTheseUsers: [],
            phoneTypes: [],
            invalidCount: 0,
        }
    },

    computed: {
        //Is the form fully filled out?
        formCompleted: function () {
            for (let index = 0; index < this.newUsers.length; index++) {

                this.isValidated(index);

                if (!this.newUsers[index].isComplete || this.newUsers[index].errorCount > 0) {
                    return false;
                }
            }

            return true;
        },

        showErrorBanner: function () {
            return this.invalidCount > 0;
        }
    },

    mounted: function () {
        let self = this;

        Vue.nextTick(function () {
            let len = cpm.existingUsers.length;

            for (let i = 0; i < len; i++) {
                Vue.set(self.newUsers, i, cpm.existingUsers[i]);
            }

            self.locations = cpm.locations;
            self.locationIds = cpm.locationIds;
            self.roles = cpm.roles;
            self.rolesMap = cpm.rolesMap;
            self.phoneTypes = cpm.phoneTypes;

            if (len < 1) {
                self.addUser();
            }
        });
    },

    methods: {
        addUser: function () {
            this.submitForm($('meta[name="submit-url"]').attr('content'));

            this.newUsers.push({
                locations: this.locationIds,
                grantAdminRights: false,
                sendBillingReports: false,
                emr_direct_address: '',
                phone_number: '',
                phone_extension: '',
                phone_type: '',
                forward_alerts_to: {
                    who: 'billing_provider',
                    user_id: ''
                },
                forward_careplan_approval_emails_to: {
                    who: 'billing_provider',
                    user_id: ''
                },
            });
        },

        deleteUser: function (index) {
            if (this.newUsers[index].id) {
                this.deleteTheseUsers.push(this.newUsers[index].id);
            }

            this.newUsers.splice(index, 1);
        },

        //Is the form for the given user filled out?
        isValidated: function (index) {
            Vue.nextTick(function () {
                createStaffVM.invalidCount = $('.invalid').length;

                createStaffVM.newUsers[index].isComplete = createStaffVM.newUsers[index].first_name
                    && createStaffVM.newUsers[index].last_name
                    && createStaffVM.newUsers[index].email
                    && createStaffVM.newUsers[index].role_id;

                createStaffVM.newUsers[index].errorCount = $('#user-' + index).find('.invalid').length;
                createStaffVM.newUsers[index].validated = createStaffVM.newUsers[index].isComplete && createStaffVM.newUsers[index].errorCount === 0;
            });


            return createStaffVM.newUsers[index].validated;
        },

        submitForm: function (url) {
            //Clear out all the errors
            this.newUsers.forEach((user) => {
                user.errorCount = 0;
            });

            this.$http.post(url, {
                deleteTheseUsers: this.deleteTheseUsers,
                users: this.newUsers
            }).then(function (response) {
                // success
                if (response.data.message) {
                    Materialize.toast(response.data.message, 4000);
                } else {
                    //render the view sent
                    $('html').html(response.data);
                }
            }, function (response) {
                //fail

                let created = response.data.created.map(function (index) {
                    createStaffVM.newUsers.splice(index, 1);
                });

                let errors = response.data.errors;

                createStaffVM.$set('invalidCount', errors.length);

                for (let i = 0; i < errors.length; i++) {
                    $('input[name="users[' + i + '][' + Object.keys(errors[i].messages)[0] + ']"]')
                        .addClass('invalid');

                    $('label[for="users[' + i + '][' + Object.keys(errors[i].messages)[0] + ']"]')
                        .attr('data-error', errors[i].messages[Object.keys(errors[i].messages)[0]][0]);

                    createStaffVM.$set('newUsers[' + i + '].errorCount', errors.length);
                }

                $("html, body").animate({scrollTop: 0}, {duration: 300, queue: false});
            });
        }
    }
});




