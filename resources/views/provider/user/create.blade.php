@extends('provider.layouts.default')

@section('title', 'Provider Dashboard')

<head>
    <style>

        .mdl-layout {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mdl-layout__content {
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .demo-card-wide > .mdl-card__title {
            color: #fff;
            height: 190px;
            background: url({{asset('/img/clh_logo.svg')}}) center / contain;
            background-repeat: no-repeat;
        }
    </style>
</head>
@section('content')
    <div class="mdl-layout mdl-js-layout">
        <main class="mdl-layout__content mdl-cell--4-col">

            <div class="mdl-card mdl-shadow--2dp demo-card-wide mdl-cell--12-col">

                <div class="mdl-card__title"></div>

                {!! Form::open(['url' => '/auth/login', 'method' => 'post']) !!}

                <div class="mdl-card__supporting-text">

                    <div class="mdl-textfield mdl-js-textfield mdl-cell--12-col">
                        <input class="mdl-textfield__input" type="text" id="sample1">
                        <label class="mdl-textfield__label" for="sample1">First name</label>
                    </div>

                    <div class="mdl-layout-spacer"></div>


                    <div class="mdl-textfield mdl-js-textfield mdl-cell--12-col">
                        <input class="mdl-textfield__input" type="text" id="sample1">
                        <label class="mdl-textfield__label" for="sample1">Last name</label>
                    </div>

                    <div class="mdl-layout-spacer"></div>

                    <div class="mdl-textfield mdl-js-textfield mdl-cell--12-col">
                        <input class="mdl-textfield__input" type="text" id="sample1">
                        <label class="mdl-textfield__label" for="sample1">E-mail</label>
                    </div>

                    <div class="mdl-layout-spacer"></div>

                    <div class="mdl-textfield mdl-js-textfield mdl-cell--12-col">
                        <input class="mdl-textfield__input" type="password" id="sample1">
                        <label class="mdl-textfield__label" for="sample1">Password</label>
                    </div>

                    <div class="mdl-layout-spacer"></div>

                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"
                            data-upgraded=",MaterialButton,MaterialRipple">
                        Create account
                        <span class="mdl-button__ripple-container"></span>
                    </button>
                </div>

                {!! Form::close() !!}

            </div>


        </main>

    </div>

@endsection