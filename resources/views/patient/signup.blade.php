@extends('provider.layouts.default')

@section('head')
    <style>

        /*#SECTION_1*/

        #SECTION_1 {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            display: flex;
            height: 680px;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 1440px;
            column-rule-color: rgb(255, 255, 255);
            align-items: center;
            perspective-origin: 720px 340px;
            transform-origin: 720px 340px;
            background: rgb(0, 154, 129) none repeat scroll 0% 0% / auto padding-box border-box;
            border: 0px none rgb(255, 255, 255);
            flex-flow: column nowrap;
            font: normal normal normal normal 20px / 30px Lato, Helvetica, Arial, sans-serif;
            outline: rgb(255, 255, 255) none 0px;
            padding: 65px 0px 20px;
        }

        #DIV_2 {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            height: 595px;
            max-width: 1200px;
            min-height: auto;
            min-width: auto;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 1035px;
            column-rule-color: rgb(255, 255, 255);
            align-self: center;
            perspective-origin: 517.5px 297.5px;
            transform-origin: 517.5px 297.5px;
            border: 0px none rgb(255, 255, 255);
            font: normal normal normal normal 20px / 30px Lato, Helvetica, Arial, sans-serif;
            margin: 0px 202.5px;
            outline: rgb(255, 255, 255) none 0px;
            padding: 20px 0px;
        }

        /*#DIV_2*/

        #H3_3 {
            bottom: 0px;
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            height: 90px;
            left: 0px;
            position: relative;
            right: 0px;
            text-align: center;
            text-rendering: geometricPrecision;
            text-transform: uppercase;
            top: 0px;
            width: 1035px;
            column-rule-color: rgb(255, 255, 255);
            perspective-origin: 517.5px 45px;
            transform-origin: 517.5px 45px;
            border: 0px none rgb(255, 255, 255);
            font: normal normal bold normal 35px / 60px Lato, Helvetica, Arial, sans-serif;
            margin: 0px;
            outline: rgb(255, 255, 255) none 0px;
            padding: 10px 0px 20px;
        }

        /*#H3_3*/

        #DIV_4 {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            display: flex;
            height: 465px;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 1035px;
            column-rule-color: rgb(255, 255, 255);
            perspective-origin: 517.5px 232.5px;
            transform-origin: 517.5px 232.5px;
            border: 0px none rgb(255, 255, 255);
            font: normal normal normal normal 20px / 30px Lato, Helvetica, Arial, sans-serif;
            outline: rgb(255, 255, 255) none 0px;
        }

        /*#DIV_4*/

        #DIV_5, #DIV_10 {
            box-shadow: rgba(77, 77, 77, 0.0980392) 0px 2px 4px 0px;
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            display: flex;
            height: 439.641px;
            max-width: 320px;
            min-height: 430px;
            min-width: auto;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 320px;
            column-rule-color: rgb(255, 255, 255);
            align-self: center;
            justify-content: space-around;
            perspective-origin: 160px 219.812px;
            transform-origin: 160px 219.812px;
            background: rgba(255, 255, 255, 0.2) linear-gradient(-180deg, rgb(50, 171, 151), rgba(255, 255, 255, 0) 98%) repeat scroll 0% 0% / auto padding-box border-box;
            border: 0px none rgb(255, 255, 255);
            flex: 1 1 0%;
            flex-flow: column nowrap;
            font: normal normal normal normal 20px / 30px Lato, Helvetica, Arial, sans-serif;
            margin: 12.5px;
            outline: rgb(255, 255, 255) none 0px;
            padding: 20px;
        }

        /*#DIV_5, #DIV_10*/

        #H4_6, #H4_11 {
            bottom: 0px;
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            height: 122px;
            left: 0px;
            min-height: auto;
            min-width: auto;
            position: relative;
            right: 0px;
            text-align: center;
            text-rendering: geometricPrecision;
            text-transform: uppercase;
            top: 0px;
            width: 280px;
            column-rule-color: rgb(255, 255, 255);
            perspective-origin: 140px 61px;
            transform-origin: 140px 61px;
            border: 0px none rgb(255, 255, 255);
            font: normal normal bold normal 37.5px / 62.5px Lato, Helvetica, Arial, sans-serif;
            margin: 0px;
            outline: rgb(255, 255, 255) none 0px;
            padding: 30px 0px;
        }

        /*#H4_6, #H4_11*/

        #DIV_7 {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            height: 90px;
            min-height: auto;
            min-width: auto;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 280px;
            column-rule-color: rgb(255, 255, 255);
            perspective-origin: 140px 45px;
            transform-origin: 140px 45px;
            border: 0px none rgb(255, 255, 255);
            font: normal normal normal normal 20px / 30px Lato, Helvetica, Arial, sans-serif;
            outline: rgb(255, 255, 255) none 0px;
        }

        /*#DIV_7*/

        #H1_8 {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            height: 90px;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 280px;
            column-rule-color: rgb(255, 255, 255);
            perspective-origin: 140px 45px;
            transform-origin: 140px 45px;
            border: 0px none rgb(255, 255, 255);
            font: normal normal 500 normal 75px / 60px Lato, Helvetica, Arial, sans-serif;
            margin: 0px;
            outline: rgb(255, 255, 255) none 0px;
            padding: 10px 0px 20px;
        }

        /*#H1_8*/

        #P_9, #P_13 {
            box-sizing: border-box;
            color: rgba(255, 255, 255, 0.6);
            height: 120px;
            min-height: auto;
            min-width: auto;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 280px;
            column-rule-color: rgba(255, 255, 255, 0.6);
            perspective-origin: 140px 60px;
            transform-origin: 140px 60px;
            border: 0px none rgba(255, 255, 255, 0.6);
            font: normal normal normal normal 23.75px / 30px Lato, Helvetica, Arial, sans-serif;
            margin: 0px;
            outline: rgba(255, 255, 255, 0.6) none 0px;
            padding: 0px 0px 30px;
        }

        /*#P_9, #P_13*/

        #H1_12, #H1_16 {
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            height: 90px;
            min-height: auto;
            min-width: auto;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 280px;
            column-rule-color: rgb(255, 255, 255);
            perspective-origin: 140px 45px;
            transform-origin: 140px 45px;
            border: 0px none rgb(255, 255, 255);
            font: normal normal 500 normal 75px / 60px Lato, Helvetica, Arial, sans-serif;
            margin: 0px;
            outline: rgb(255, 255, 255) none 0px;
            padding: 10px 0px 20px;
        }

        /*#H1_12, #H1_16*/

        #DIV_14 {
            box-shadow: rgba(77, 77, 77, 0.0980392) 0px 2px 4px 0px;
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            display: flex;
            height: 440px;
            max-width: 320px;
            min-height: 430px;
            min-width: auto;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 320px;
            column-rule-color: rgb(255, 255, 255);
            align-self: center;
            justify-content: space-around;
            perspective-origin: 160px 220px;
            transform-origin: 160px 220px;
            background: rgba(255, 255, 255, 0.2) linear-gradient(-180deg, rgb(50, 171, 151), rgba(255, 255, 255, 0) 98%) repeat scroll 0% 0% / auto padding-box border-box;
            border: 0px none rgb(255, 255, 255);
            flex: 1 1 0%;
            flex-flow: column nowrap;
            font: normal normal normal normal 20px / 30px Lato, Helvetica, Arial, sans-serif;
            margin: 12.5px;
            outline: rgb(255, 255, 255) none 0px;
            padding: 20px;
        }

        /*#DIV_14*/

        #H4_15 {
            bottom: 0px;
            box-sizing: border-box;
            color: rgb(255, 255, 255);
            height: 120px;
            left: 0px;
            min-height: auto;
            min-width: auto;
            position: relative;
            right: 0px;
            text-align: center;
            text-rendering: geometricPrecision;
            text-transform: uppercase;
            top: 0px;
            width: 280px;
            column-rule-color: rgb(255, 255, 255);
            perspective-origin: 140px 60px;
            transform-origin: 140px 60px;
            border: 0px none rgb(255, 255, 255);
            font: normal normal bold normal 22.5px / 30px Lato, Helvetica, Arial, sans-serif;
            margin: 0px;
            outline: rgb(255, 255, 255) none 0px;
            padding: 30px 0px;
        }

        /*#H4_15*/

        #P_17 {
            box-sizing: border-box;
            color: rgba(255, 255, 255, 0.6);
            height: 120px;
            min-height: auto;
            min-width: auto;
            text-align: center;
            text-rendering: geometricPrecision;
            width: 280px;
            column-rule-color: rgba(255, 255, 255, 0.6);
            perspective-origin: 140px 60px;
            transform-origin: 140px 60px;
            border: 0px none rgba(255, 255, 255, 0.6);
            font: normal normal normal normal 18.75px / 30px Lato, Helvetica, Arial, sans-serif;
            margin: 0px;
            outline: rgba(255, 255, 255, 0.6) none 0px;
            padding: 0px 0px 30px;
        }

        /*#P_17*/
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
@endsection

@section('content')

    <div class="content">

        <div class="navbar-fixed">
            <nav class="white">
                <div class="header nav-wrapper">
                    <a href="#" class="col s2"><img class="logo" src="{{asset('/img/clh_logo.svg')}}" alt=""></a>
                    <ul id="nav-mobile" class="right hide-on-med-and-down">
                        <a class="waves-effect waves-light btn-large blue">Sign me up</a>
                    </ul>
                </div>
            </nav>
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

        <div class="section">
            <div class="row">
                <div class="col s4">
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
                <div class="col s4">
                    <div class="center promo promo-picture">
                        <img class="col s12" src="{{asset('/img/landing-pages/careplan.png')}}" alt="">
                        <p class="col s12 promo-caption">Care Plan and Unlimited Educational Info</p>
                        <p class="col s12 light center promo-paragraph">Our nurses formulate a care plan based on
                            doctor’s orders
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

        <div class="section">
            <div class="row">
                <section id="SECTION_1">
                    <div id="DIV_2">
                        <h3 id="H3_3">
                            Proven Results and Engagement
                        </h3>
                        <div id="DIV_4">
                            <div id="DIV_5">
                                <h4 id="H4_6">
                                    Diabetes
                                </h4>
                                <div id="DIV_7">
                                    <h1 id="H1_8">
                                        18%
                                    </h1>
                                </div>
                                <p id="P_9">
                                    A1c improvement (1.7% absolute change) versus control group
                                </p>
                            </div>
                            <div id="DIV_10">
                                <h4 id="H4_11">
                                    HIV
                                </h4>
                                <h1 id="H1_12">
                                    44%
                                </h1>
                                <p id="P_13">
                                    Of patients improved from detectable to undetectable viral loads
                                </p>
                            </div>
                            <div id="DIV_14">
                                <h4 id="H4_15">
                                    Irritable Bowel Syndrome
                                </h4>
                                <h1 id="H1_16">
                                    +80%
                                </h1>
                                <p id="P_17">
                                    Over 80% patient engagement when prompted weekly or bi-weekly
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <form>
            <div class="section container">
                <h1 class="center">Sign Up for Wellness Manager</h1>

                <br>
                <br>

                <div class="row">
                    <div class="input-field col s6">
                        <i class="material-icons prefix">account_circle</i>
                        <input id="first_name" type="text" class="validate" required>
                        <label for="first_name">First Name</label>
                    </div>
                    <div class="input-field col s6">
                        <input id="last_name" type="text" class="validate" required>
                        <label for="last_name">Last Name</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">phone</i>
                        <input id="text" type="text" class="validate"
                               pattern="\d{3}[\-]\d{3}[\-]\d{4}" required>
                        <label for="text" data-error="The phone number must match format xxx-xxx-xxxx">Phone Number
                            xxx-xxx-xxxx</label>
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">email</i>
                        <input id="email" type="email" class="validate">
                        <label for="email" data-error="The email must contain an @">Email</label>
                    </div>
                </div>

                <div class="row">
                    <div class="input-field col s12">
                        <i class="material-icons prefix">message</i>
                        <textarea id="textarea1" class="materialize-textarea">

Best time to call:

Allergies:

Conditions List:

Medications List:

                        </textarea>
                        <label for="textarea1">Message</label>
                    </div>

                    <div class="row">
                        <div class="input-field col s12 center">
                            <a class="waves-effect waves-light btn-large blue center">Reach out to me</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
                    <h5 class="white-text">Olark live chat, maybe?</h5>
                    {{--<ul>--}}
                    {{--<li><a class="grey-text text-lighten-3" href="#!">Link 1</a></li>--}}
                    {{--<li><a class="grey-text text-lighten-3" href="#!">Link 2</a></li>--}}
                    {{--<li><a class="grey-text text-lighten-3" href="#!">Link 3</a></li>--}}
                    {{--<li><a class="grey-text text-lighten-3" href="#!">Link 4</a></li>--}}
                    {{--</ul>--}}
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <div class="container">
                © 2017 Copyright Text
                <a class="grey-text text-lighten-4 right" href="#!">Call CLH maybe?</a>
            </div>
        </div>
    </footer>


@endsection


