var Vue = require('vue');

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

Vue.directive("select", {
    "twoWay": true,

    "bind": function () {
        $(this.el).material_select();

        var self = this;

        $(this.el).on('change', function () {
            self.set($(self.el).val());
        });
    },

    update: function (newValue, oldValue) {
        $(this.el).val(newValue);
    },

    "unbind": function () {
        $(this.el).material_select('destroy');
    }
});

// Vue.component('user', {
//     props: [
//         'id',
//         'email',
//         'last_name',
//         'first_name',
//         'phone_number',
//         'phone_type',
//         'isComplete',
//         'validated',
//         'errorCount',
//         'role_id',
//         'locations'
//     ],
//
//     template: '<h2>{{first_name}}, {{role_id}}</h2>'
//
// });

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
            newUsers: [],
            roles: [],
            rolesMap: [],
            deleteTheseUsers: [],
            phoneTypes: [],
            invalidCount: 0
        }
    },

    computed: {
        //Is the form fully filled out?
        formCompleted: function () {
            for (var index = 0; index < this.newUsers.length; index++) {

                this.isValidated(index);

                if (!this.newUsers[index].isComplete || this.newUsers[index].errorCount > 0) {
                    return false;
                }
            }

            return true;
        },

        showErrorBanner: function () {
            if (this.invalidCount > 0) {
                return true;
            }
        }
    },

    ready: function () {
        for (var i = 0, len = cpm.existingUsers.length; i < len; i++) {
            this.newUsers.$set(i, cpm.existingUsers[i]);
        }

        this.$set('locations', cpm.locations);
        this.$set('locationIds', cpm.locationIds);
        this.$set('roles', cpm.roles);
        this.$set('rolesMap', cpm.rolesMap);
        this.$set('phoneTypes', cpm.phoneTypes);

        if (len < 1) {
            this.addUser();
        }
    },

    methods: {
        addUser: function () {
            this.newUsers.push({
                locations: this.locationIds,
                grandAdminRights: false,
                sendBillingReports: false
            });

            this.$nextTick(function () {
                $('select').material_select();
                $('.collapsible').collapsible();
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
            this.$set('invalidCount', $('.invalid').length);

            this.$set('newUsers[' + index + '].isComplete', this.newUsers[index].first_name
                && this.newUsers[index].last_name
                && this.newUsers[index].email
                && this.newUsers[index].phone_number
                && this.newUsers[index].role_id
                && this.newUsers[index].phone_type
            );

            this.$set('newUsers[' + index + '].errorCount', $('#user-' + index).find('.invalid').length);
            this.$set('newUsers[' + index + '].validated', this.newUsers[index].isComplete && this.newUsers[index].errorCount == 0);

            return this.newUsers[index].validated;
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
                $('html').html(response.data);
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




