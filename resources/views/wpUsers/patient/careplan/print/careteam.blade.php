@include('vue-templates.care-team')

<care-team v-bind:care-team-collection="careTeamCollection"></care-team>

@section('scripts')
    <script src="/js/view-care-plan.js"></script>
@endsection