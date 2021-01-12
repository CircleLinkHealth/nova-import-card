<style>
    label {
        font-size: 14px;
    }

    .providerForm {
        padding: 10px;
    }

</style>

<template>
    <ul class="col-xs-12">
        <li v-for="(care_person, index) in careTeamCollection" :key="index" class="col-xs-12">
            <div v-show="care_person.user.first_name && care_person.user.last_name">
                <div class="col-md-7"><p style="margin-left: -10px;"><strong>{{care_person.formatted_type}}
                    : </strong>{{care_person.user.first_name}} {{care_person.user.last_name}}
                    <em>{{care_person.user.primaryRole}}</em></p></div>
                <div class="col-md-3"><p v-if="care_person.alert">Receives Alerts</p></div>
                <div class="col-md-2">
                    <button :id="'deleteCareTeamMember-' + care_person.id"
                            class="btn btn-xs btn-danger problem-delete-btn"
                            v-on:click.stop.prevent="deleteCarePerson(care_person, index)"><span> <i
                            class="glyphicon glyphicon-remove"></i> </span></button>
                    <button class="btn btn-xs btn-primary problem-edit-btn"
                            v-on:click.stop.prevent="editCarePerson(care_person)"><span> <i
                            class="glyphicon glyphicon-pencil"></i> </span></button>
                </div>
            </div>
            <care-person v-bind:care_person="care_person"></care-person>
        </li>
    </ul>
</template>

<script>
    let Vue = require('vue');
    Vue.use(require('vue-resource'));
    Vue.http.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

    require('./care-person.vue');

    // This is the event hub we'll use in every
    // component to communicate between them.
    let eventHub = new Vue();

    let careTeam = Vue.component('careTeam', {

        props: {
            careTeamCollection:Array
        },

        data: function () {
            return {
                destroyRoute: $('meta[name="provider-destroy-route"]').attr('content')
            }
        },

        methods: {
            deleteCarePerson: function (carePerson, index) {
                let disassociate = confirm('Are you sure you want to remove ' + carePerson.user.first_name
                    + ' '
                    + carePerson.user.last_name + ' from the CareTeam?');

                if (!disassociate) {
                    return true;
                }

                this.$http.delete(careTeam.destroyRoute + '/' + carePerson.id).then(function (response) {
                    careTeam.careTeamCollection.splice(index, 1);
                }, function (response) {
                    //error
                });
            },

            editCarePerson: function (carePerson) {
                $("#editCareTeamModal-" + carePerson.id).modal({    backdrop: 'static',    keyboard: false});
            },
        }
    });

    module.exports = careTeam;

</script>
