@extends('layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'Toledo Invitation')
@section('activity', 'Toledo Invitation')
@section('content')
    <div class="container">
        <div class="letter-view">
            @if(!$hideButtons)
                <div class="header-buttons">
                    @include('enrollment-letters.enrollableInvitationButtons')
                </div>
            @endif

            @include('enrollment-letters.headers.woodlands')

            @include('enrollment-letters.baseLetter')

            @if(!$hideButtons)
                <div class="header-buttons">
                    @include('enrollment-letters.enrollableInvitationButtons')
                </div>
            @endif
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
