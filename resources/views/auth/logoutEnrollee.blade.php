@extends('layouts.surveysMaster')

@section('content')
    <div class="container">
        <div class="practice-logo">
            <div class="logo">
                <div class="practice-logo">
                    <img style="height: 120px;"
                         src="{{$practiceLogoSrc}}"
                         alt="{{$practiceName}}"/>
                </div>
            </div>
        </div>
        <div class="message">
            <hr>
            <h4>Done! You can close this window.</h4>
        </div>
    </div>
@endsection
<style>
    .message {
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
    .practice-logo{
        text-align: center;
        padding-top: 40px;
    }
</style>
