@extends('provider.layouts.default')

@section('title', 'Signup for Wellness Management')
@section('meta-image-url', "{{asset('/img/landing-pages/coordination-with-family.png')}}")

@section('head')
    <style>
        .logo {
            height: 50px;
            margin-top: 6px;
        }

        .overlay {
            padding-top: 5%;
            height: 800px;
        }

        .header {
            margin: 0px 14px 0px 40px;
            max-height: 100px;
        }
    </style>

    <meta property="og:url" content="{{url('patient-sign-up-for-chronic-care-management/')}}"/>
    <meta property="og:title" content="Do you or a loved one need a hand managing chronic conditions?"/>
    <meta property="og:image" content="{{asset('/img/landing-pages/coordination-with-family.png')}}"/>
    <meta property="og:image:secure_url" content="{{asset('/img/landing-pages/coordination-with-family.png')}}"/>
    <meta property="og:image:width" content="1050"/>
    <meta property="og:image:height" content="550"/>
    <meta property="og:type" content="image.other"/>

    <meta property="og:image" content="{{asset('/img/landing-pages/coordination-with-family.png')}}"/>
    <meta property="og:image:secure_url" content="{{asset('/img/landing-pages/coordination-with-family.png')}}"/>
    <meta property="og:image:type" content="image/jpeg"/>
    <meta property="og:image:width" content="1050"/>
    <meta property="og:image:height" content="550"/>
@endsection

@section('content')

    <div class="content">

        <div class="navbar-fixed">
            <nav class="white">
                <div class="header nav-wrapper">
                    <a href="#" class="col s2"><img class="logo" src="{{asset('/img/clh_logo.svg')}}" alt=""></a>
                </div>
            </nav>
        </div>

        <div class="col s12 blue lighten-1">
            <div class="row overlay"
                 style="background: url('http://www.circlelinkhealth.com/wp-content/themes/CLH_132/images/bg.png')">

                <br>
                <br>
                <br>

                <h4 class="white-text center">Thank you for reaching out.</h4>

                <br>
                <br>

                <h5 class="blue-grey-text text-lighten-5 center">We look forward to talking with you.</h5>

            </div>

        </div>

    </div>
@endsection

@section('scripts')
@endsection


