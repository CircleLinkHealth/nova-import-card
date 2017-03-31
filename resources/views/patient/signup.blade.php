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
            height: 450px;
        }

        .header {
            margin: 0px 14px 0px 40px;
            max-height: 100px;
        }

        .promo-picture {
            max-height: 70px;
        }

        .signup-form {
            /*height: 360px;*/
            box-shadow: 0px 2px 23px #555;
        }

        .speech-bubble {
            border-radius: 5px;
            padding: 10px;
            width: 42rem;
            height: 7rem;
        }

        h5 {
            line-height: 100%;
            font-size: 1.5rem;
            margin: .7rem 0 .6rem;
        }

        .title {
            line-height: 165%;
            margin: 0;
            font-size: 2.5rem;
        }
    </style>

    <meta property="og:url" content="{{url('sign-up/')}}"/>
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
                    <div class="row">
                        <div class="col s1">
                            <img class="logo" src="{{asset('/img/clh_logo.svg')}}">
                        </div>

                        <div class="col s10">
                            <h1 class="center title blue-text">Wellness Manager</h1>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <div class="row blue lighten-1">
            <div class="col s12 m9 overlay valign-wrapper"
                 style="background: url('http://www.circlelinkhealth.com/wp-content/themes/CLH_132/images/bg.png')">

                <div class="valign">
                    <h5 class="white-text center">Let our registered nurse care coaches take the stress out of chronic
                        condition for you <u>or</u> a loved one.</h5>

                    <div class="center" style="margin-top: 4%;">
                        <h5 class="teal-text text-accent-1 center">Telephone health coaching, care plan setup and
                            coordination with family and doctors. <br><br>Just $49.99/month!</h5>
                    </div>
                </div>
            </div>


            <div class="col s12 m3 valign-wrapper overlay">
                {!! Form::open(['url' => route('sign-up.store'), 'method' => 'post', 'class' => 'col s12 valign white signup-form', 'id' => 'signup-form']) !!}

                <div class="row">
                    <div class="input-field col s12">
                        {{--<i class="material-icons prefix">account_circle</i>--}}
                        <input id="name" type="text" class="validate" required name="name">
                        <label for="name" id="name_label">Name</label>
                    </div>


                    <div class="input-field col s12">
                        {{--<i class="material-icons prefix">phone</i>--}}
                        <input id="text" type="text" class="validate"
                               pattern="\d{3}[\-]\d{3}[\-]\d{4}" required name="phone">
                        <label for="text" data-error="The phone number must match format xxx-xxx-xxxx">Phone Number
                            xxx-xxx-xxxx</label>
                    </div>


                    <div class="input-field col s12">
                        {{--<i class="material-icons prefix">email</i>--}}
                        <input id="email" type="email" name="email" class="validate">
                        <label for="email" data-error="The email must contain an @">Email</label>
                    </div>

                    <div class="input-field col s12">
                        {{--<i class="material-icons prefix">message</i>--}}
                        <textarea id="textarea1" class="materialize-textarea" name="comment"
                                  placeholder=""></textarea>
                        <label for="textarea1">Message (Optional)</label>
                    </div>

                    <div class="center">
                        <button type="submit"
                                class="waves-effect waves-light btn-large teal accent-3">Sign me up
                        </button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>


        </div>

        <div class="section">
            <div class="row">
                <div class="col s6">
                    <div class="speech-bubble right blue lighten-4">
                        <h5>“They call it personalized care and that’s really how it feels.”</h5>
                        <p class="right">- User in Charlotte, North Carolina</p>
                    </div>
                </div>

                <div class="col s6">
                    <div class="speech-bubble left blue lighten-4">
                        <h5>“What a great service!”</h5>
                        <p class="right">- Dr. Jeffrey Hyman, Medical Director, UPG / Northwell Health (New
                            York)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <h5 class="center">Care protocols built with top clinician advisors</h5>
                <div class="col s12 m4 center">
                    <div class="center">
                        <p>Instructor at</p>
                        <img class="promo-picture" src="{{asset('/img/harvard-medical-school.png')}}">
                    </div>
                </div>
                <div class="col s12 m4 center">
                    <div class="center">
                        <p>Medical Director at</p>
                        <img class="promo-picture" src="{{asset('/img/keck-medical-center.jpg')}}">
                    </div>
                </div>
                <div class="col s12 m4 center">
                    <div class="center">
                        <p>Chief of Medicine at</p>
                        <img class="promo-picture" src="{{asset('/img/brickham-and-womens-hospital.jpg')}}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="page-footer teal" style="padding-top: 0;">
        <div class="footer-copyright">
            <div class="container">
                © 2017 CicleLink Health
            </div>
        </div>
    </footer>
@endsection

@section('scripts')

@endsection


