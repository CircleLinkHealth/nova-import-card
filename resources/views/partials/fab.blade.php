<meta name="route.patient.note.create" content="{{ route('patient.note.create', ['patient' => $patient->id]) }}">
<meta name="route.patient.observation.create"
      content="{{ route('patient.observation.create', ['patient' => $patient->id]) }}">
<meta name="route.patient.activity.create"
      content="{{ route('patient.activity.create', ['patient' => $patient->id]) }}">
<meta name="route.patient.appointment.create"
      content="{{ route('patient.appointment.create', ['patientId' => $patient->id]) }}">
<meta name="provider-update-route" content="{{ route('user.care-team.update', ['userId' => $patient->id, 'id'=>'']) }}">
<meta name="patient_id" content="{{$patient->id}}">
@if($patient->carePlan)
    <meta name="patient_careplan_id" content="{{$patient->carePlan->id}}">
@endif
<meta name="providers-search-route" content="{{ route('providers.search') }}">

<div id="v-fab">
    <fab ref="fabComponent"></fab>
</div>




