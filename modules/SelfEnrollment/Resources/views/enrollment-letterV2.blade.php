@extends('selfEnrollment::layouts.EnrollmentSurvey.enrollmentLetterMaster')
@section('title', 'SelfEnrollment LetterV2')
@section('activity', 'SelfEnrollment LetterV2')
@section('content')
    <div class="container">
        <div class="letter-view">
            @if(!$hideButtons)
                <div class="header-buttons">
                    @include('selfEnrollment::enrollment-letters.enrollableInvitationButtonsV2')
                </div>
            @endif
            <br>
                <div class="practice-logo" style="text-align: {{$letter->logoPosition()}}; height: {{$letter->logoDistanceFromText()}}">
                    <img src="{{$letter->logoUrl()}}" alt="{{$practiceName}}" style="height: {{$letter->logoSize()}}"/>
                    <br>
                </div>

                <div class="letter-body">
                    {!! $letter->body() !!}
                    <br>
                </div>

                @foreach($letter->signatures() as $signature)
                    <div>
                        <img src="{{$signature->signatureUrl()}}" style="height: 86px;" alt="{{$practiceName}}"/>
                    </div>
                    <div>
                        {{$signature->providerName()}} {{$signature->providerSpecialty()}}
                    </div>
                    <div>
                       {!! $signature->signatoryTitleAttributes() !!}
                    </div>
                @endforeach

            @if(!$hideButtons)
                <div class="header-buttons">
                    @include('selfEnrollment::enrollment-letters.enrollableInvitationButtonsV2')
                </div>
            @endif
        </div>
    </div>
@endsection

<style>
    .practice-logo img{
        max-height: 120px;
        opacity: 95%;
        margin-right: 50px;
    }

    .letter-view {
        font-size: 18px;
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .header-buttons{
        text-align: center;
    }

    @media (max-width: 490px) {
        .practice-logo img{
            max-height: 80px;
            padding-bottom: 10px;
        }

        .practice-logo{
            text-align: center !important;
            margin-right: unset;
        }

        .letter-view{
            font-size: 16px;
        }

    }

</style>