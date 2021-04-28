<div class="row" style="margin-bottom: 5px">
    <div class="enroll-now-href">
        <a href="{{route('patient.self.enroll.now', ['enrollable_id' => $userEnrolleeId, 'is_survey_only' => $isSurveyOnlyUser])}}"
           disabled="{{$disableButtons}}" onclick="preventIfDisabled({{$disableButtons}})">
            <button type="button" class="btn btn-large" style="border-radius: 40px; background-color: {{$buttonColor}}">Get my Care Coach</button>
        </a>
    </div>
    <div class="request-info-href">
        <a href="{{route('patient.requests.enroll.info', ['enrollable_id' => $userEnrolleeId, 'is_survey_only' => $isSurveyOnlyUser])}}"
           disabled="{{$disableButtons}}" onclick="preventIfDisabled({{$disableButtons}})">
            Or get more info
        </a>
    </div>
</div>
<script>
    function preventIfDisabled($disableButtons){
        if($disableButtons){
            event.preventDefault();
            alert("Buttons are disabled during Enrolment Letter Review.");
        }
    }
</script>