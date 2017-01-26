@include('vue-templates.care-person')

<script type="text/x-template" id="care-team-container-template">
    <ul class="col-xs-12">
        <div v-for="member in careTeamCollection">
            <li class="col-xs-12">
                <care-person v-bind:care_person="member"></care-person>
            </li>
        </div>
    </ul>
</script>