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
            height: 410px;
        }

        .header {
            margin: 0px 14px 0px 40px;
            max-height: 100px;
        }

        .section {
            padding-top: 3rem;
            padding-bottom: 1rem;
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
                    <a href="#" class="col s2"><img class="logo" src="{{asset('/img/clh_logo.svg')}}" alt=""></a>
                    <ul id="nav-mobile" class="right hide-on-med-and-down">
                        <a class="waves-effect waves-light btn-large scroll-to-form blue">Sign me up</a>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="col s12 blue lighten-1">
            <div class="row overlay"
                 style="background: url('http://www.circlelinkhealth.com/wp-content/themes/CLH_132/images/bg.png')">

                <h1 style="line-height: 110%;font-size: 1.64rem;margin: .82rem 0 .656rem;" class="white-text center"><b>WELLNESS
                        MANAGER</b> <em>from CircleLink Health</em></h1>

                <br>

                <h5 class="white-text center">Let our registered nurse care coaches take the stress out of chronic
                    condition for you <u>or</u> a loved one.</h5>

                <div class="center" style="margin-top: 4%;">
                    <h5 class="teal-text text-accent-1 center">Telephone health coaching, care plan setup and
                        coordination with family and doctors. Just $49.99/month!</h5>
                    <br>
                    <a class="waves-effect waves-light btn-large scroll-to-form teal accent-3">Sign me up</a>
                </div>
            </div>

        </div>

        {!! Form::open(['url' => route('sign-up.store'), 'method' => 'post', 'class' => 'col s12', 'id' => 'signup-form']) !!}
        <div class="section container">
            <h1 class="center">Sign Up for Wellness Manager</h1>

            <br>
            <br>

            <div class="row">
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">account_circle</i>
                    <input id="first_name" type="text" class="validate" required name="first_name">
                    <label for="first_name" id="first_name_label">First Name</label>
                </div>
                <div class="input-field col s12 m6">
                    <i class="material-icons prefix">account_box</i>
                    <input id="last_name" type="text" class="validate" required name="last_name">
                    <label for="last_name">Last Name</label>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">phone</i>
                    <input id="text" type="text" class="validate"
                           pattern="\d{3}[\-]\d{3}[\-]\d{4}" required name="phone">
                    <label for="text" data-error="The phone number must match format xxx-xxx-xxxx">Phone Number
                        xxx-xxx-xxxx</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">email</i>
                    <input id="email" type="email" name="email" class="validate">
                    <label for="email" data-error="The email must contain an @">Email</label>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">message</i>
                    <textarea id="textarea1" class="materialize-textarea" name="comment"
                              placeholder="Let us know when to call you. It would great if you could include information regarding the patient's Allergies, Conditions, and current Medications List."></textarea>
                    <label for="textarea1">Message</label>
                </div>

                <div class="row">
                    <div class="input-field col s12 center">
                        <button class="btn-large waves-effect waves-light blue center" type="submit" name="action">
                            Schedule a call
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {!! Form::close() !!}

    </div>

    <footer class="page-footer teal" style="padding-top: 0;">
        {{--<div class="container">--}}
        {{--<div class="row">--}}
        {{--<div class="col l6 s12">--}}
        {{--<h5 class="white-text">Footer Content</h5>--}}
        {{--<p class="grey-text text-lighten-4">You can use rows and columns here to organize your--}}
        {{--footer--}}
        {{--content.</p>--}}
        {{--</div>--}}
        {{--<div class="col l4 offset-l2 s12">--}}
        {{--<h5 class="white-text">Olark live chat, maybe?</h5>--}}
        {{--<ul>--}}
        {{--<li><a class="grey-text text-lighten-3" href="#!">Link 1</a></li>--}}
        {{--<li><a class="grey-text text-lighten-3" href="#!">Link 2</a></li>--}}
        {{--<li><a class="grey-text text-lighten-3" href="#!">Link 3</a></li>--}}
        {{--<li><a class="grey-text text-lighten-3" href="#!">Link 4</a></li>--}}
        {{--</ul>--}}
        {{--</div>--}}
        {{--</div>--}}
        {{--</div>--}}
        <div class="footer-copyright">
            <div class="container">
                © 2017 CicleLink Health
                {{--<a class="grey-text text-lighten-4 right" href="#!">Call CLH maybe?</a>--}}
            </div>
        </div>
    </footer>


@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $(".scroll-to-form").click(function () {
                $('html, body').animate({
                    scrollTop: $("#signup-form").offset().top
                }, 600);

                $("#first_name_label").trigger("click");
            });
        });
    </script>
@endsection


