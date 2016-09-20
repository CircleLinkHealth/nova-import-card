@extends('provider.layouts.default')

@section('title', 'Create Program Lead')

<head>
    <style>

        html, body {
            height: 100%;
        }

        body {
            display: flex;
            align-items: center;
        }

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

    @include('errors.errors')

    <div class="mdl-layout mdl-js-layout">
        <main class="mdl-layout__content mdl-cell--4-col">

            <div class="mdl-card mdl-shadow--1dp demo-card-wide mdl-cell--12-col">

                <div class="mdl-card__title"></div>

                {!! Form::open(['url' => route('post.store.user'), 'method' => 'post']) !!}

                <div class="mdl-card__supporting-text">

                    <div class="mdl-textfield mdl-js-textfield mdl-cell--12-col">
                        <input class="mdl-textfield__input" type="text" id="firstName" name="firstName">
                        <label class="mdl-textfield__label" for="firstName">First name</label>
                    </div>

                    <div class="mdl-layout-spacer"></div>


                    <div class="mdl-textfield mdl-js-textfield mdl-cell--12-col">
                        <input class="mdl-textfield__input" type="text" id="lastName" name="lastName">
                        <label class="mdl-textfield__label" for="lastName">Last name</label>
                    </div>

                    <div class="mdl-layout-spacer"></div>

                    <div class="mdl-textfield mdl-js-textfield mdl-cell--12-col">
                        <input class="mdl-textfield__input" type="text" id="email" name="email">
                        <label class="mdl-textfield__label" for="email">E-mail</label>
                    </div>

                    <div class="mdl-layout-spacer"></div>

                    <div class="mdl-textfield mdl-js-textfield mdl-cell--12-col">
                        <input class="mdl-textfield__input" type="password" id="password" name="password">
                        <label class="mdl-textfield__label" for="password">Password</label>
                    </div>

                    <div class="mdl-layout-spacer"></div>

                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary">
                        Create account
                    </button>
                </div>

                {!! Form::close() !!}

            </div>


        </main>

    </div>

@endsection