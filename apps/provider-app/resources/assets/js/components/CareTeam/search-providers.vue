<template>
    <div v-if="matchedUsers.length>0 && show" class="alert alert-info"><h4>Did you mean?</h4>
        <ul>
            <li v-for="(user, index) in matchedUsers" :key="index"><a href="#"
                                                @click.stop.prevent="attachExistingProvider(user)">{{user.first_name}} {{user.last_name}}, {{user.primary_practice.display_name}}</a>
            </li>
        </ul>
    </div>
</template>

<script>
    export default {
        props: ['first_name', 'last_name'],

        data: function () {
            return {
                matchedUsers: [],
                getSearchUrl: '',
                show: true,
            }
        },

        mounted: function () {
            this.getSearchUrl = $('meta[name="providers-search-route"]').attr('content');
        },

        computed: {
            validFullName: function () {
                return this.first_name.length > 2 || this.last_name.length > 2;
            },

            fullName: function () {
                return this.first_name + ' ' + this.last_name;
            }
        },

        methods: {
            search: function () {
                let self = this

                let url = this.getSearchUrl + '?firstName=' + this.first_name + '&lastName=' + this.last_name;

                this.axios.get(url).then(function (response) {
                    self.matchedUsers = response.data.results;
                }, function (response) {
                    //error
                });
            },

            attachExistingProvider: function (user) {
                this.$emit('existing-user-selected', user)
                this.show = false
            }
        },

        watch: {
            fullName: function () {
                if (this.validFullName) {
                    this.search();
                }
            }
        }
    }
</script>