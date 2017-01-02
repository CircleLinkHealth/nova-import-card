<style>

    @media print {
        .hidden-print, .hidden-print * {
            display: none !important;
        }
    }

</style>

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<section class="FAB hidden-print">
    <div class="FAB__mini-action-button hidden-print">
        <div class="mini-action-button--hide mini-action-button hidden-print">
            <a href="{{  URL::route('patient.note.create', array('patient' => $patient->id)) }}">
                <i class="mini-action-button__icon material-icons">speaker_notes</i>
            </a>
            <p class="mini-action-button__text--hide">Add Note</p>
        </div>

        <div class="mini-action-button--hide mini-action-button">
            <a href="{{ URL::route('patient.observation.create', array('patient' => $patient->id)) }}">
                <i class="mini-action-button__icon material-icons">timeline</i>
            </a>
            <p class="mini-action-button__text--hide">Add Observation</p>
        </div>

        <div class="mini-action-button--hide mini-action-button">
            <a href="{{ URL::route('patient.activity.create', array('patient' => $patient->id)) }}">
                <i class="mini-action-button__icon material-icons">local_hospital</i>
            </a>
            <p class="mini-action-button__text--hide">Add Offline Activity</p>
        </div>


        <div class="mini-action-button--hide mini-action-button">
            <a href="{{ URL::route('patient.appointment.create', array('patientId' => $patient->id)) }}">
                <i class="mini-action-button__icon material-icons">today</i>
            </a>
            <p class="mini-action-button__text--hide">Add Appointment</p>
        </div>

    </div>
    <div class="FAB__action-button hidden-print">
        <i class="action-button__icon material-icons hidden-print">add</i>
    </div>
</section>