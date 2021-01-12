<div class="row" style="margin-bottom: 5px">
        <div class="enroll-now-href">
            <a href="{{route('patient.self.enroll.now', ['enrollable_id' => $userEnrollee['id'], 'is_survey_only' => $isSurveyOnlyUser])}}">
                <button type="button" class="btn btn-large" style="border-radius: 40px; background-color: {{$buttonColor}}">Get my Care Coach</button>
            </a>
        </div>

        <div class="request-info-href">
            <a href="{{route('patient.requests.enroll.info', ['enrollable_id' => $userEnrollee['id'], 'is_survey_only' => $isSurveyOnlyUser])}}">
                Or get more info
            </a>
        </div>
</div>
