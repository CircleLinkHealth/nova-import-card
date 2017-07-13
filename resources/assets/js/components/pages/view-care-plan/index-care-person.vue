<template>
    <div v-show="carePerson.user.first_name && carePerson.user.last_name">
        <div class="col-md-7"><p style="margin-left: -10px;"><strong>{{carePerson.formatted_type}}
            : </strong>{{carePerson.user.first_name}} {{carePerson.user.last_name}}
            <em>{{carePerson.user.primaryRole}}</em></p></div>
        <div class="col-md-3"><p v-if="carePerson.alert">Receives Alerts</p></div>
        <div class="col-md-2">
            <!--<button :id="'deleteCareTeamMember-' + carePerson.id"-->
                    <!--class="btn btn-xs btn-danger problem-delete-btn"-->
                    <!--v-on:click.stop.prevent="deleteCarePerson(carePerson, index)"><span> <i-->
                    <!--class="glyphicon glyphicon-remove"></i> </span></button>-->
            <!--<button class="btn btn-xs btn-primary problem-edit-btn"-->
                    <!--v-on:click.stop.prevent="editCarePerson(carePerson)"><span> <i-->
                    <!--class="glyphicon glyphicon-pencil"></i> </span></button>-->
        </div>
    </div>
</template>

<script>
    export default {
        props: ['carePerson']
    }
</script>