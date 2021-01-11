@extends('layouts.app')

@push('styles')
    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Nunito', sans-serif;
            font-weight: 200;
            height: 100vh;
            margin: 0;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
            margin-top: 200px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

    </style>
@endpush

@section('content')
    <div class="flex-center position-ref">
        <div class="content">
            <div class="title">
                <p>Annual Wellness Visit</p>
            </div>
            <div class="by-circlelink text-center">
                ⚡️ by CircleLink Health
            </div>
        </div>
    </div>
    <div class="text-center" style="margin-top: 100px">
        <a class="btn btn-primary btn-lg" href="{{config('services.cpm.url')}}">
            Looking for CarePlanManager™ ? Click Here!
        </a>
    </div>
@endsection
