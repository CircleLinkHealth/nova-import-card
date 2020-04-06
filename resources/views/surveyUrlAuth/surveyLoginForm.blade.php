@extends('layouts.surveysMaster')
@section('content')
    <div class="container main-container login">
        <div class="survey-container login">
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
                        @if($isEnrolleeSurvey)
                            Almost done! <br> Just need some information
                        @else
                            Annual Wellness <br> Survey Login
                        @endif

                    </div>
                </div>
                <div class="survey-sub-welcome-text">
                    Before we get started, we just need to verify your identity first.
                </div>

                <form method="POST" action="{{route('auth.login.with.signed')}}">
                    {{csrf_field()}}
                    <div style="margin-top: 20px;">
                        <div class="form-group form-group-input">
                            <label for="full-name" class="full-name">Full Name</label>
                            <br>
                            <input type="text" name="name"
                                   class="login-inputs"
                                   value="{{old('name')}}"
                                   placeholder="Full Name" required>

                        </div>
                        <br>
                        <div class="form-group form-group-input">
                            <label for="birth-date" class="birth-date">Date of Birth (DOB)</label>
                            <br>
                            <input type="date" name="birth_date"
                                   class="login-inputs"
                                   placeholder="1950-01-15" required
                                   value="{{old('birth_date')}}">

                        </div>
                        <br>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                            <br/>
                        @endif

                        <input type="hidden" name="url" value="{{$urlWithToken}}">
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>

                </form>
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

        .main-container.login {
            height: initial;
        }

        .survey-container.login {
            height: initial;
        }

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
            width: 100%;
        }

        .survey-container {
            margin: 20px auto auto;
            width: 500px;
            height: 90%;
            border-radius: 5px;
            border: solid 1px #f2f2f2;
            background-color: #ffffff;
        }

        @media (max-width: 490px) {
            .main-container {
                height: 560px;
                /*margin-top: 125px;*/
                /*margin-left: 7px;*/
            }

            .survey-container {
                width: 330px;
            }

            .full-name {
                font-size: 14px;
            }

            .birth-date {
                font-size: 14px;
            }

            .login-inputs {
                height: 50px;
            }

            .practice-title {
                font-size: 14px;
            }

            #sub-title {
                margin-top: -42px;
                font-size: 21px;
                font-weight: 400;
                margin-bottom: 20px;
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

            .form-group {
                margin-bottom: -5px;
                margin-top: -3px;
            }

            .btn-primary {
                height: 42px;
                padding-top: 12px
            }

            .by-circlelink {
                margin-top: -15px;
                font-size: 12px;
                margin-left: 19px;
            }
        }
    </style>
@endpush
