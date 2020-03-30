@extends('layouts.EnrollmentSurvey.enrollmentSurveyMaster')
@section('title', 'Enrollment Invitation')
@section('activity', 'Enrollment Invitation')
@section('content')
    <div class="container">
        <div class="letter-view">
            @if(!$hideButtons)
                @include('enrollment-consent.enrollableInvitationButtons')
            @endif
            <div class="headers">
                @include('enrollment-consent.practiceLogo')
                <br>
                <hr>
                <div class="letter">
                    <div class="header">
                        {{$signatoryNameForHeader}}
                        <br>
                        {{$practiceName}}
                    </div>

                    <div class="letter-sent">
                        {{$dateLetterSent}}
                    </div>
                    <div class="letter-head">
                        Dear {{$userEnrollee->first_name}},
                    </div>
                    <div class="letter-body">
                        <div class="body">
                            @include('enrollment-consent.enrollmentLetter')
                        </div>
                        <footer>
                            @include('enrollment-consent.practiceLogo')
                        </footer>
                    </div>
                    @if(!$hideButtons)
                        @include('enrollment-consent.enrollableInvitationButtons')
                    @endif
                </div>
            </div>
        </div>
    </div>


@endsection

<style>
    .enroll-now-href {
        padding: 4px;
        margin-left: 420px;
    }

    .request-info-href {
        padding: 4px;
        padding-left: 20px;
    }

    .buttons {
        /*padding-bottom: 10px;*/
    }

    .letter {
        width: fit-content;
        max-height: 590px;
        overflow-y: scroll;
    }

    .headers {
        padding-top: 10px;
    }

    .letter-sent {
        float: right;
        font-weight: 500;
        margin-right: 17em;
        font-size: 20px;
    }

    .letter-head {
        font-family: Tahoma;
        padding-bottom: 15px;
        font-size: 20px;
        font-weight: bold;
    }

    .letter-body {
        font-family: Tahoma;
        text-align: left;
        font-size: 20px;
    }

    .body {

    }

    .letter-view {
        /*width: 520px;*/
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .footer {
        margin-top: 40px;
    }

    .header {
        padding-bottom: 30px;
    }

    @media (max-width: 490px) {
        .headers {
            padding-top: 10px;
            width: 504px;
        }

        .enroll-now-href {
            padding: 4px;
            margin-left: 130px;
            padding-right: 20px;
        }

        .letter-view {
            width: 504px;
        }

        .letter {
            width: fit-content;
            max-height: 560px;
            overflow-y: scroll;
        }

        .letter-sent {
            margin-right: 2em;
        }
    }

</style>