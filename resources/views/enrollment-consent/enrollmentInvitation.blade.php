@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Enrollment Invitation')
@section('activity', 'Enrollment Invitation')
@section('content')
    <div class="container">
        <div class="letter-view">
            @if(!$hideButtons)
                <div class="header-buttons">
                    @include('enrollment-consent.enrollableInvitationButtons')
                </div>
            @endif
            <div class="headers">
                <div class="logo" style="{{$logoStyleRequest}}">
                    @include('enrollment-consent.practiceLogo')
                </div>
                <br>
                <hr>
            </div>
                <div class="flow-text" style="max-height: 590px; overflow-y: scroll;">
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
                    </div>
                    <div class="logo" style="margin-bottom: 10px">
                        <div class="logo">
                            @include('enrollment-consent.practiceLogo')
                        </div>
                    </div>
                    @if(!$hideButtons)
                        <div class="header-buttons">
                            @include('enrollment-consent.enrollableInvitationButtons')
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


@endsection

<style>
    .logo {
        text-align: center;
    }

    .request-info-href {
        padding: 10px;
        text-decoration: underline;
        font-size: 17px;
    }

    .letter {
        width: fit-content;
        max-height: 590px;
        overflow-y: scroll;
    }

    /*.headers {
        padding-top: 10px;
    }*/

    .letter-sent {
        float: right;
        font-weight: 500;
        margin-right: 8em;
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

    .letter-view {
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .header {
        padding-bottom: 30px;
    }

    .header-buttons{
        text-align: center;
    }

    @media (max-width: 490px) {
        /*.letter-view {
            width: 504px;
        }*/

        .letter {
            width: fit-content;
            min-height: 1073px;
            overflow-y: scroll;
        }

        .letter-sent {
            margin-right: 2em;
        }

    }

</style>
