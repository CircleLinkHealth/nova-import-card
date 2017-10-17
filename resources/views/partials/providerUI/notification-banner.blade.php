<div class="alert alert-warning alert-dismissible notification-banner" role="alert">
    <div class="container">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <br>
        <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>

        <span class="banner-title">Sorry to interrupt, we just need a clarification.</span>
        <br>
        <br>
        <p class="banner-body">
            This patient has Diabetes, but it is not specified whether it is Type 1 or Type 2. Please visit the
            <u>
                <a href="{{route('patient.careplan.show', ['patientId' => $patient->id, 'page' => 1]) . '#user-header-problems-checkboxes'}}">
                    Edit Care Plan
                </a>
            </u>
            page and check either "Diabetes Type 1" or "Diabetes Type 2" under "Diagnosis / Problems to Monitor".
        </p>
    </div>
</div>