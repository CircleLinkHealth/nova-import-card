<meta name="route.patient.note.create" content="{{ route('patient.note.create', [$patient->id]) }}">
<meta name="route.patient.observation.create"
      content="{{ route('patient.observation.create', [$patient->id]) }}">
<meta name="route.patient.activity.create"
      content="{{ route('patient.activity.create', [$patient->id]) }}">
<meta name="route.offline-activity-time-request.create"
      content="{{ route('offline-activity-time-requests.create', [$patient->id]) }}">
<meta name="route.patient.appointment.create"
      content="{{ route('patient.appointment.create', [$patient->id]) }}">
<meta name="provider-update-route" content="{{ route('user.care-team.update', [$patient->id]) }}">
<meta name="patient_id" content="{{$patient->id}}">
@if($patient->carePlan)
    <meta name="patient_careplan_id" content="{{$patient->carePlan->id}}">
@endif
<meta name="providers-search-route" content="{{ route('providers.search') }}">

<div id="v-fab">
    <fab :can-add-offline-activity="@json($canAddOfflineActivity)" ref="fabComponent"></fab>
</div>

@push('scripts')
    <script>
        window['patientId'] = @json($patient->id);

        @if ($patient->primaryPractice)
            window['patientPractice'] = {
                id: @json($patient->primaryPractice->id),
                name: @json($patient->primaryPractice->display_name)
            };
        @endif

    </script>
@endpush

