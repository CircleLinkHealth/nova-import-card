@extends('layouts.surveysMaster')
@section('content')
    <div class="container main-container">

        <div class="survey-container">
            <div class="practice-title">
                <div id="title">
                    <strong>{{$practiceName}}</strong>
                    <br/>
                    Dr. {{$doctorsLastName}}’s Office
                </div>
            </div>
            <div class="card-body">
                <div class="survey-main-title">
                    <div id="sub-title">
                        Annual Wellness Survey Login
                    </div>
                </div>
                <div class="survey-sub-welcome-text">
                    Before we get started, we just need to verify your identity first.
                </div>

                <div style="margin-top: 20px;">
                    <div class="form-group form-group-input">
                        <label for="full-name" class="full-name">Full Name</label><br>
                        <input type="text" name="name" style="width: 400px; height: 60px; border-radius: 5px;"
                               placeholder="Full Name" required>
                    </div>
                    <br>
                    <div class="form-group form-group-input">
                        <label for="birth-date" class="birth-date">Date of Birth</label>
                        <input type="date" name="birth_date" style="width: 400px; height: 60px; border-radius: 5px;"
                               placeholder="1950-01-15" required>
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
        </div>
    </div>
@endsection

@push('styles')
    <style>

        .form-group-input {
            text-align: left;
        }

        label {
            font-family: Poppins, serif;
            font-size: 18px;
            font-weight: 500;
            font-style: normal;
            font-stretch: normal;
            line-height: normal;
            letter-spacing: 1px;
            color: #1a1a1a;
        }

        input {
            padding-left: 20px;
            padding-top: 16px;
            padding-bottom: 16px;
            border-radius: 5px;
            border: solid 1px #f2f2f2;
            background-color: #ffffff;
        }

        .survey-container {
            margin: 20px auto auto;
            width: 500px;
            height: 90%;
            border-radius: 5px;
            border: solid 1px #f2f2f2;
            background-color: #ffffff;
        }
    </style>
@endpush
