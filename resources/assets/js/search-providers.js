var Vue = require('vue');
Vue.use(require('vue-resource'));
Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

let searchProviders = Vue.component('searchProviders', {
        template: '<div v-if="matchedCarePeople.length>0" class="alert alert-info"><h4>Did you mean?</h4><ul><li v-for="care_person in matchedCarePeople"><a href="" v-on:click="updateCareTeamMember(care_person.id)">{{care_person.first_name}} {{care_person.last_name}}, {{care_person.primary_practice.display_name}}</a></li></ul></div>',

        props: ['first_name', 'last_name'],

        data: function () {
            return {
                matchedCarePeople: [],
                getSearchUrl: ''
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
                    this.$set('matchedCarePeople', response.data.results);
                }, function (response) {
                    //error
                });
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