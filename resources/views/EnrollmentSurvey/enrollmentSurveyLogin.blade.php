@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Auto enrollment Login')
@section('activity', 'Auto enrollment Login')
@section('content')
    <div class="container">
            <br>
        <main>
                <center>
                    <div class="container">
                            <div class="z-depth-3 y-depth-3 x-depth-3 grey green-text lighten-4 row"
                                 style="display: inline-block;
                              padding: 32px 30px 0px 30px;
                              border: 1px;
                              margin-top: 10px;
                              solid: #EEE;">
                                <div class="practice-title">
                                    <div id="title">
                                        <strong>{{$practiceName}}</strong>
                                        <br/>
                                        {{$doctorsLastName}}’s Office
                                    </div>
                                </div>
                                <br>
                                <div class="card-body" style="min-height: 550px; max-width: 355px;">
                                    <div class="survey-sub-welcome-text">
                                        Before we get started, we just need to verify your identity first.
                                    </div>
                                    <br>
                                    <form method="POST" action="{{route('invitation.enrollment.login')}}" class="form-prevent-multi-submit">
                                        {{csrf_field()}}

{{--                                        <div class='row' style="margin-top: 70px;">--}}
{{--                                            <div class='input-field col s12'>--}}
{{--                                                <label for="display_name" class="full-name" style="font-size: 20px; color:black">Full Name</label>--}}
{{--                                                <input type="text" name="display_name"--}}
{{--                                                       class="login-inputs"--}}
{{--                                                       value="{{old('display_name')}}"--}}
{{--                                                       placeholder="Enter your full Name"--}}
{{--                                                       required>--}}
{{--                                            </div>--}}
{{--                                        </div>--}}
                                        <div class='row' style="margin-top: 80px;">
                                            <div class='input-field col s12' style="text-align: left">
                                                <label for="birth-date" class="birth-date" style="font-size: 16px; position: unset; color:black">Please Enter Your Date of Birth</label>
                                                <input type="date" name="birth_date"
                                                       class="login-inputs"
                                                       placeholder="1950-01-15"
                                                       value="{{old('birth_date')}}"
                                                       required>
                                            </div>
                                        </div>
                                        <br/>
                                        <center>
                                            <div class='row'>
                                                @if ($errors->any())
                                                    <div class="alert alert-danger" style="color: red">
                                                        @foreach ($errors->all() as $error)
                                                            <div>{{ $error }}</div>
                                                        @endforeach
                                                    </div>
                                                    <br/>
                                                @endif

                                                <input type="hidden" name="user_id" value="{{$userId}}">
                                                <input type="hidden" name="is_survey_only" value="{{$isSurveyOnly}}">
                                                <input type="hidden" name="url_with_token" value="{{$urlWithToken}}">
                                                <button type="submit" id="submit" class="waves-effect waves-light btn-large btn-prevent-multi-submit" style="background-color: #4CB2E1">
                                                    Continue
                                                </button>
                                            </div>
                                        </center>
                                    </form>

                                </div>
                </center>
            </main>
@endsection

@push('styles')
    <style>
        .survey-sub-welcome-text {
            font-size: 20px;
            display: contents;
            color: black;
            letter-spacing: 1px;
        }
        .practice-title {
            /*width: 300px;
            height: 58px;*/
            font-family: Poppins, sans-serif;
            font-size: 18px;
            letter-spacing: 1.5px;
            text-align: center;
            margin-top: 20px;
            margin-bottom: 10px;
            /*margin-left: 111px;*/
            color: #50b2e2;
        }

        .full-name {
            font-family: Poppins, sans-serif;
            font-size: 18px;
            font-weight: 500;
            font-style: normal;
            font-stretch: normal;
            line-height: normal;
            letter-spacing: 1px;
            color: #1a1a1a;
        }

        .birth-date {
            font-family: Poppins, sans-serif;
            font-size: 18px;
            font-weight: 500;
            font-style: normal;
            font-stretch: normal;
            line-height: normal;
            letter-spacing: 1px;
            color: #1a1a1a;
        }
        @media (max-width: 490px) {
            .practice-title {
                font-size: 14px;
            }


            .survey-sub-welcome-text {
                font-size: 14px;
                word-break: 87px;
                display: contents;
            }

            .login-inputs {
                height: 39px;
                font-size: 13px;
            }

            .birth-date {
                font-size: 12px;
            }

            .full-name {
                font-size: 12px;
            }

        }
    </style>
@endpush
