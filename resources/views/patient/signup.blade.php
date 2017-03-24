@extends('provider.layouts.default')

@section('head')
    <style>
        .logo {
            height: 60px;

        }

        .overlay {
            padding-top: 5%;
            height: 410px;
        }

        .header {
            margin: 14px 14px 10px 40px;;
            max-height: 100px;
        }
    </style>
@endsection

@section('content')

    <div class="content">
        <div class="header row">
            <div class="col s12">
                <div class="col s2">
                    <img class="logo" src="{{asset('/img/clh_logo.svg')}}" alt="">
                </div>
                <div class="col s8"></div>
                <div class="col s2">
                    <a class="waves-effect waves-light btn-large blue">Sign me up</a>
                </div>
            </div>
        </div>

        <div class="col s12 blue lighten-1">
            <div class="row overlay"
                 style="background: url('http://www.circlelinkhealth.com/wp-content/themes/CLH_132/images/bg.png')">

                <h4 class="white-text center">Do you or a loved one need a hand managing chronic conditions?</h4>

                <div class="col s12">
                    <div class="divider" style="width: 13%;
    margin: 1px auto 15px auto;"></div>
                </div>


                <h5 class="blue-grey-text text-lighten-5 center">Put your mind at ease with our fully licensed
                    registered nurse care coaches just a phone call away.</h5>


                <div class="center" style="margin-top: 6%;">
                    <a class="waves-effect waves-light btn-large light-blue accent-1">Sign me up</a>
                </div>
            </div>

        </div>

        @if($version == 1)
            <div class="section">
                @elseif($version == 2)
                    <div class="container">
                        @endif

                        <div class="row">
                            <div class="col s4">
                                <div class="center promo promo-picture">
                                    <img class="col s12" src="{{asset('/img/landing-pages/registered-nurse.png')}}"
                                         alt="">
                                    <p class="promo-caption">Fully Registered Nurse Care Coach</p>
                                    <p class="light center">Our trained nurses guide you to wellness while taking the
                                        stress off
                                        managing chronic illness.</p>
                                </div>
                            </div>
                            <div class="col s4">
                                <div class="center promo promo-picture">
                                    <img class="col s12" src="{{asset('/img/landing-pages/careplan.png')}}" alt="">
                                    <p class="promo-caption">Care Plan and Unlimited Educational Info</p>
                                    <p class="light center">Our nurses formulate a care plan based on doctor’s orders
                                        and we
                                        provide
                                        unlimited access to wellness and diet content/education materials.</p>
                                </div>
                            </div>
                            <div class="col s4">
                                <div class="center promo promo-picture">
                                    <img class="col s12"
                                         src="{{asset('/img/landing-pages/coordination-with-family.png')}}"
                                         alt="">
                                    <p class="promo-caption">Coordination with Family and Doctors</p>
                                    <p class="light center">Family members can stay at ease with real-time updates from
                                        our
                                        system
                                        as our nurses track progress. Your participating doctors also stay updated.</p>
                                </div>
                            </div>
                        </div>
                    </div>

            </div>

            <footer class="page-footer">
                <div class="container">
                    <div class="row">
                        <div class="col l6 s12">
                            <h5 class="white-text">Footer Content</h5>
                            <p class="grey-text text-lighten-4">You can use rows and columns here to organize your
                                footer
                                content.</p>
                        </div>
                        <div class="col l4 offset-l2 s12">
                            <h5 class="white-text">Links</h5>
                            <ul>
                                <li><a class="grey-text text-lighten-3" href="#!">Link 1</a></li>
                                <li><a class="grey-text text-lighten-3" href="#!">Link 2</a></li>
                                <li><a class="grey-text text-lighten-3" href="#!">Link 3</a></li>
                                <li><a class="grey-text text-lighten-3" href="#!">Link 4</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="footer-copyright">
                    <div class="container">
                        © 2017 Copyright Text
                        <a class="grey-text text-lighten-4 right" href="#!">More Links</a>
                    </div>
                </div>
            </footer>


@endsection


