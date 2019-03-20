@extends('surveysMaster')
@section('content')
    <link href="{{asset('css/surveyLoginHra.css')}}" rel="stylesheet">
    <form method="POST" action="{{route('surveyLoginAuth')}}">
        {{csrf_field()}}
        <div class="survey-login">
            <div class="practice-title">
                <label for="title">[Practice Name]
                    Dr. [doctor last name]’s Office</label>
            </div>
            <div class="survey-main-title">
                <label for="title">Annual Wellness
                    Survey Login</label>
            </div>
            <div class="survey-sub-title">Before we get started, we just need to verify your identity first.</div>

            <div style="margin-top: 60px;">
                <div class="form-group form-group-input">
                    <label for="full-name" class="full-name">Full Name</label><br>
                    <input type="text" name="name" style="width: 400px; height: 60px; border-radius: 5px;"
                           placeholder="Full Name" required>
                </div>
                <br>
                <div class="form-group form-group-input">
                    <label for="birth-date" class="birth-date">Date of Birth</label>
                    <input type="date" name="birth_date" style="width: 400px; height: 60px; border-radius: 5px;"
                           placeholder="1969-02-15" required>
                </div>
                <br>
                <input type="hidden" name="url" value="{{$urlWithToken}}">
                <button type="submit" class="btn btn-primary">Continue</button>
            </div>
            <br>

            <div class="by-circlelink">
                ⚡️ by CircleLink Health
            </div>
        </div>
    </form>

@endsection