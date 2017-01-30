@include('vue-templates.care-person')

<ul class="col-xs-12">
    <div v-for="member in careTeamCollection">
        <li class="col-xs-12">
            <care-person v-bind:care_person="member"></care-person>
        </li>
    </div>
</ul>

@section('scripts')
    <script src="/js/view-care-plan.js"></script>
@endsection