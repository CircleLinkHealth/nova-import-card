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

        .promo-caption {
            font-size: 1.6rem;
        }

        .promo-paragraph {
            font-size: 1.4rem;
        }

        .promo {
            padding-top: 3rem;
            padding-bottom: 1rem;
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
                        <a class="waves-effect waves-light btn-large scroll-to-form blue">Talk to us</a>
                    </ul>
                </div>
            </nav>
        </div>

        <div class="col s12 blue lighten-1">
            <div class="row overlay"
                 style="background: url('http://www.circlelinkhealth.com/wp-content/themes/CLH_132/images/bg.png')">

                <h5 class="white-text center"><b>WELLNESS MANAGER</b> <em>from CircleLink Health</em></h5>

                <br>

                <h5 class="white-text center">Let our registered nurse care coaches take
                    the stress out of chronic
                    conditions for you <u>or</u> for a loved one.</h5>

                <div class="center" style="margin-top: 4%;">
                    <h5 class="teal-text text-accent-1 center">Telephone health coaching, care plan setup and
                        coordination with family and doctors for just $49.99/month!</h5>
                    <br>
                    <a class="waves-effect waves-light btn-large scroll-to-form teal accent-3">Talk to us</a>
                </div>
            </div>

        </div>

        <div class="section">
            <div class="row">
                <div class="col s12 m4 center">
                    <div class="center promo promo-picture">
                        <img class="col s12" src="{{asset('/img/landing-pages/registered-nurse.png')}}"
                             alt="">
                        <p class="col s12 promo-caption">Fully Registered Nurse Care Coach</p>
                        <p class="col s12 light center promo-paragraph">Our trained nurses guide you or a loved one to
                            wellness
                            while taking the
                            stress off managing chronic illness.</p>
                    </div>
                </div>
                <div class="col s12 m4 center">
                    <div class="center promo promo-picture">
                        <img class="col s12" src="{{asset('/img/landing-pages/careplan.png')}}" alt="">
                        <p class="col s12 promo-caption">Care Plan/Unlimited Educational Info</p>
                        <p class="col s12 light center promo-paragraph">Our nurses formulate a care plan based on
                            doctor’s orders
                            and we
                            provide
                            unlimited access to wellness and diet content/education materials.</p>
                    </div>
                </div>
                <div class="col s12 m4 center">
                    <div class="center promo promo-picture">
                        <img class="col s12"
                             src="{{asset('/img/landing-pages/coordination-with-family.png')}}"
                             alt="">
                        <p class="col s12 promo-caption">Coordination with Family and Doctors</p>
                        <p class="col s12 light center promo-paragraph">Family members can stay at ease with real-time
                            updates from
                            our
                            system
                            as our nurses track progress. Your participating doctors also stay updated.</p>
                    </div>
                </div>
            </div>
        </div>

        {!! Form::open(['url' => route('sign-up.store'), 'method' => 'post', 'class' => 'col s12', 'id' => 'signup-form']) !!}
        <div class="section container">
            <h2 class="center">Learn more about Wellness Manager</h2>
            <p class="center">We will be giving you a call to discuss the benefits of our program with you.</p>
            <br>
            <br>

            <div class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">account_circle</i>
                    <input id="name" type="text" class="validate" required name="name">
                    <label for="name" id="name_label">Name</label>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s12">
                    <i class="material-icons prefix">phone</i>
                    <input id="text" type="text" class="validate"
                           required name="phone">
                    <label for="text" data-error="The phone number must match format xxx-xxx-xxxx">Phone Number</label>
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
                              placeholder=""></textarea>
                    <label for="textarea1">Message (Optional)</label>
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


