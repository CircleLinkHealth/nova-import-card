{{--The component's css--}}
<style>
    label {
        font-size: 14px;
    }

    .providerForm {
        padding: 10px;
    }

</style>

{{--Declare any variables the component may need here--}}
{{--In this case I need routes to be able to delete multiple components--}}
<meta name="provider-destroy-route" content="{{ route('care-team.destroy', ['id'=>'']) }}">

{{--The component's Template--}}
<script type="text/x-template" id="care-team-template">
    <ul class="col-xs-12">
        <li v-for="care_person in careTeamCollection" class="col-xs-12">
            <div v-show="care_person.user.first_name && care_person.user.last_name">
                <div class="col-md-7">
                    <p style="margin-left: -10px;">
                        <strong>@{{care_person.formatted_type}}
                            : </strong>@{{care_person.user.first_name}} @{{care_person.user.last_name}}
                        <em>@{{care_person.user.provider_info.specialty}}</em>
                    </p>
                </div>

                <div class="col-md-3">
                    <p v-if="care_person.alert">Receives Alerts</p>
                </div>

                <div class="col-md-2">
                    <button id="deleteCareTeamMember-@{{care_person.id}}"
                            class="btn btn-xs btn-danger problem-delete-btn"
                            v-on:click.stop.prevent="deleteCarePerson(care_person, $index)">
                    <span>
                        <i class="glyphicon glyphicon-remove"></i>
                    </span>
                    </button>
                    <button class="btn btn-xs btn-primary problem-edit-btn"
                            v-on:click.stop.prevent="editCarePerson(care_person)">
            <span>
                <i class="glyphicon glyphicon-pencil"></i>
            </span>
                    </button>
                </div>
            </div>

            <care-person v-bind:care_person="care_person"></care-person>
        </li>
    </ul>
</script>