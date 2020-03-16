@extends('layouts.pdf')

<style>
    h4 {
        color: black !important;
    }
</style>

@section('content')
    <div class="container">

        <div class="text-right">
            <img style="float: right;" src="{{public_path('/img/logos/LogoHorizontal_Color.svg')}}" width="170" height="70" class="img-responsive" alt="CLH Logo">
        </div>

        <div class="clearfix"></div>

        <br>
        <br>

        <div style="width: 100%;">
            <h4>
            <span style="float: left;">
                To: Dr. {{ $provider->getFullName() }}
            </span>
                <span style="float: right;">
                {{ $note->created_at }}
            </span>
                <br>
                From: {{ $sender->getFullName() }}
            </h4>
        </div>

        <br>

        <h4>
            Re: {{ $patient->getFullName() }} &#124; DOB: {{ $patient->getBirthDate() }} &#124; {{ $patient->getGender() }}
            &#124; {{$patient->getAge()}} yrs &#124; {{ $patient->getPhone() }}
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
            {{ $note->body }}
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