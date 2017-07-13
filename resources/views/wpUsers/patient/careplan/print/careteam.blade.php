{{--Declare any variables the component may need here--}}
{{--In this case I need routes to be able to delete multiple components--}}
<meta name="provider-destroy-route" content="{{ route('care-team.destroy', ['id'=>'']) }}">

<meta name="provider-update-route" content="{{ route('care-team.update', ['id'=>'']) }}">
<meta name="providers-search" content="{{ route('providers.search') }}">
<meta name="created_by" content="{{auth()->user()->id}}">
<meta name="patient_id" content="{{$patient->id}}">

<care-team v-bind:care-team-collection="careTeamCollection"></care-team>

@section('scripts')
    {{--<script src="/js/view-care-plan.js"></script>--}}
@endsection