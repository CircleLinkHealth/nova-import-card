<meta name="route.patient.note.create" content="{{ URL::route('patient.note.create', ['patient' => $patient->id]) }}">
<meta name="route.patient.observation.create"
      content="{{ URL::route('patient.observation.create', ['patient' => $patient->id]) }}">
<meta name="route.patient.activity.create"
      content="{{ URL::route('patient.activity.create', ['patient' => $patient->id]) }}">
<meta name="route.patient.appointment.create"
      content="{{ URL::route('patient.appointment.create', ['patientId' => $patient->id]) }}">
<meta name="provider-update-route" content="{{ route('care-team.update', ['id'=>'']) }}">
<meta name="patient_id" content="{{$patient->id}}">

<div id="app-fab">
    <fab></fab>
</div> <!-- end #app -->