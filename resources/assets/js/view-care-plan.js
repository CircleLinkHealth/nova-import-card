var Vue = require('vue');

Vue.config.debug = true;

Vue.use(require('vue-resource'));

Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

var careTeamContainer = Vue.component('careTeamContainer', {
    template: '#care-team-template',

    data: function () {
        return {
            careTeamCollection: [],
            destroyRoute: '',
            updateRoute: '',
        }
    },

    ready: function () {
        for (var i = 0, len = cpm.careTeam.length; i < len; i++) {
            this.careTeamCollection.$set(i, cpm.careTeam[i]);
        }

        this.$set('destroyRoute', $('meta[name="provider-destroy-route"]').attr('content'));
        this.$set('updateRoute', $('meta[name="provider-update-route"]').attr('content'));
    },

    methods: {
        deleteCareTeamMember: function (id, index) {
            let disassociate = confirm('Are you sure you want to disassociate this provider?');

            if (!disassociate) {
                return true;
            }

            this.$http.delete(this.destroyRoute + '/' + id).then(function (response) {
                this.careTeamCollection.splice(index, 1);
            }, function (response) {
                //error
            });
        },

        editCareTeamMember: function (id, index) {
            $("#editCareTeamModal-" + index).modal();
        },

        updateCareTeamMember: function (id, index) {
            this.$http.patch(this.updateRoute + '/' + id, {careTeamMember: this.careTeamCollection[index]}).then(function (response) {

            }, function (response) {
                //error
            });
        }
    }
});

/**
 *
 * VUE INSTANCE
 *
 */
var vm = new Vue({
    el: 'body'
});




