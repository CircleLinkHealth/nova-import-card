@extends('layouts.pdf')

<style>
    h4 {
        color: black !important;
    }

    .image.clh-logo {
        background: url({{ asset('img/logos/LogoHorizontal_Color.svg', isProductionEnv()) }}) no-repeat;
        margin-top: 1px;
        width: 170px;
        height: 70px;
    }
</style>

@section('content')
    <div class="container">
        <div class="text-left">
            <div class="img-responsive image clh-logo"></div>
        </div>

        <div class="clearfix"></div>

        <br>
        <br>

        <div class="row">
            <h4 class="col-md-12 text-left">
                Performed at: {{ presentDate($note->performed_at, true, true, true) }}
            </h4>
        </div>

        <div class="row">
            <h4 class="col-md-12 text-left">
                To: Dr. {{ $provider->getFullName() }}
            </h4>
        </div>

        <div class="row">
            <h4 class="col-md-12 text-left">
                From: {{ $sender->getFullName() }}
            </h4>
        </div>

        <br>

        <div class="row">
            <h4 class="col-md-12 text-left">
                Re: {{ $patient->getFullName() }} &#124; DOB: {{ $patient->getBirthDate() }}
                &#124; {{ $patient->getGender() }}
                &#124; {{$patient->getAge()}} yrs &#124; {{ $patient->getPhone() }}
            </h4>
        </div>

        <br>

        <div class="row">
            <h4 class="col-md-12">
                Chronic conditions tracked:
                <ul>
                    @foreach($problems as $problem)
                        <li>{{ $problem }}</li>
                    @endforeach
                </ul>
            </h4>
        </div>

        <br>

        <div class="row">
            <h4 class="col-md-12">
                <b>Note</b>:
                {{ $note->body }}
            </h4>
        </div>

        <br>
        <br>

        <div class="row">
            <h4 class="col-md-12">
                With regards,
            </h4>

            <h4 class="col-md-12">
                CircleLink Team
            </h4>
        </div>
    </div>
@endsection