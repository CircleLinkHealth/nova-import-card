@extends('layouts.pdf')

<style>
    h4 {
        color: black !important;
    }
</style>

@section('content')
    <div class="container">

        <div class="text-right">
            <img style="float: right;" src="{{ public_path('img/logo.svg') }}" width="170" height="70"
                 class="img-responsive" alt="CLH Logo">
        </div>

        <div class="clearfix"></div>

        <br>
        <br>

        <h4>
            <span style="float: left;">
                To: Dr. {{ $provider->fullName }}
            </span>
            <span style="float: right;">
                {{ $note->created_at }}
            </span>
            <br>
            From: {{ $sender->fullName }}
        </h4>

        <br>

        <h4>
            Re: {{ $patient->fullName }} &#124; DOB: {{ $patient->birthDate }} &#124; {{ $patient->gender }}
            &#124; {{$patient->age}} yrs &#124; {{ $patient->phone }}
        </h4>

        <br>

        <h4>
            Chronic conditions tracked:
            <ul>
                @foreach($problems as $problem)
                    <li>{{ $problem }}</li>
                @endforeach
            </ul>
        </h4>

        <br>

        <h4>
            Note:
            <em>{{ $note->body }}</em>
        </h4>

        <br>
        <br>


        <h4>
            With regards,
        </h4>

        <h4>
            CircleLink Team
        </h4>
    </div>
@endsection