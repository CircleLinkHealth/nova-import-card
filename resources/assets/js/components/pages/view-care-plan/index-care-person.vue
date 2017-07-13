<template>
    <div>
        WORKING!
    </div>
    <!--<div v-show="care_person.user.first_name && care_person.user.last_name">-->
        <!--<div class="col-md-7"><p style="margin-left: -10px;"><strong>{{care_person.formatted_type}}-->
            <!--: </strong>{{care_person.user.first_name}} {{care_person.user.last_name}}-->
            <!--<em>{{care_person.user.primaryRole}}</em></p></div>-->
        <!--<div class="col-md-3"><p v-if="care_person.alert">Receives Alerts</p></div>-->
        <!--<div class="col-md-2">-->
            <!--<button :id="'deleteCareTeamMember-' + care_person.id"-->
                    <!--class="btn btn-xs btn-danger problem-delete-btn"-->
                    <!--v-on:click.stop.prevent="deleteCarePerson(care_person, index)"><span> <i-->
                    <!--class="glyphicon glyphicon-remove"></i> </span></button>-->
            <!--<button class="btn btn-xs btn-primary problem-edit-btn"-->
                    <!--v-on:click.stop.prevent="editCarePerson(care_person)"><span> <i-->
                    <!--class="glyphicon glyphicon-pencil"></i> </span></button>-->
        <!--</div>-->
    <!--</div>-->
</template>

<script>
    export default {
        props: ['carePerson']
    }
</script>