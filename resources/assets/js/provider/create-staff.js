var Vue = require('vue');

Vue.use(require('vue-resource'));

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
            this.newUsers.splice(index, 1);
        }
    }
});





