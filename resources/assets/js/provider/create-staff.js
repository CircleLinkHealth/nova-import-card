var Vue = require('vue');

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

/**
 *
 * CREATE STAFF VUE INSTANCE
 *
 */
var createStaffVM = new Vue({
    el: '#create-staff-component',

    data: function () {
        return {
            newUsers: [],
            roles: [],
            deleteTheseUsers: [],
        }
    },

    computed: {
        //Is the form fully filled out?
        formCompleted: function () {
            for (var index = 0; index < this.newUsers.length; index++) {

                if (!this.newUsers[index].isComplete || this.newUsers[index].errorCount > 0) {
                    return false;
                }
            }

            return true;
        },

        invalidCount: function () {
            return $('.invalid').length;
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

        this.$set('roles', cpm.roles);

        this.newUsers.push({});
    },

    methods: {
        addUser: function () {
            this.newUsers.push({});

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

                let errors = response.data.errors;

                createStaffVM.$set('invalidCount', errors.length);

                for (let i = 0; i < errors.length; i++) {
                    $('input[name="users[' + i + '][' + Object.keys(errors[i].messages)[0] + ']"]')
                        .addClass('invalid')
                        .focus();

                    $('label[for="users[' + i + '][' + Object.keys(errors[i].messages)[0] + ']"]')
                        .attr('data-error', errors[i].messages[Object.keys(errors[i].messages)[0]][0]);

                    createStaffVM.$set('newUsers[' + i + '].errorCount', errors.length);
                }

                $("html, body").animate({scrollTop: 0}, {duration: 300, queue: false});
            });
        }
    }
});




