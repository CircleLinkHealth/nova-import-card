<div class="buttons col-lg-12">
    <div class="row">
        <div class="enroll-now-href">
            <a href="{{route('patient.self.enroll.now', ['enrollable_id' => $userEnrollee['id'], 'is_survey_only' => $isSurveyOnlyUser])}}">
                <button type="button" class="btn btn-success">Enroll Now</button>
            </a>
        </div>

        <div class="request-info-href">
            <a href="{{route('patient.requests.enroll.info', ['enrollable_id' => $userEnrollee['id'], 'is_survey_only' => $isSurveyOnlyUser])}}">
                <button type="button" class="btn btn-warning">Request Call</button>
            </a>
        </div>
    </div>
</div>

<style>

</style>