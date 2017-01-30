var Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

let searchProviders = Vue.component('searchProviders', {
        template: '<div v-if="matchedUsers.length>0" class="alert alert-info"><h4>Did you mean?</h4><ul><li v-for="user in matchedUsers"><a href="#" v-on:click="attachExistingProvider(user)">{{user.first_name}} {{user.last_name}}, {{user.primary_practice.display_name}}</a></li></ul></div>',

        props: ['first_name', 'last_name'],

        data: function () {
            return {
                matchedUsers: [],
                getSearchUrl: '',
            }
        },

        ready: function () {
            this.getSearchUrl = $('meta[name="providers-search"]').attr('content');
        },

        computed: {
            validFullName: function () {
                return this.first_name.length > 2;
            },

            fullName: function () {
                return this.first_name + ' ' + this.last_name;
            }
        },

        methods: {
            search: function () {

                let url = this.getSearchUrl + '?firstName=' + this.first_name + '&lastName=' + this.last_name;

                this.$http.get(url).then(function (response) {
                    this.$set('matchedUsers', response.data.results);
                }, function (response) {
                    //error
                });
            },

            attachExistingProvider: function (user_obj) {
                this.$dispatch('existing-user-selected', {
                    user: user_obj,
                })
            }
        },

        watch: {
            fullName: function () {
                if (this.validFullName) {
                    this.search();
                }
            }
        }
    })
    ;

module.exports = searchProviders;