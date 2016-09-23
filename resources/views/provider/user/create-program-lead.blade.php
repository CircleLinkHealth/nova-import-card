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

                {!! Form::open([
                    'url' => route('post.store.user'),
                    'method' => 'post',
                    'id' => 'registration',
                ]) !!}

                <div class="mdl-card__supporting-text">

                    @include('provider.partials.mdl.form.text.textfield', [
                        'name' => 'firstName',
                        'label' => 'First name',
                        'class' => 'mdl-cell--12-col'
                    ])

                    @include('provider.partials.mdl.form.text.textfield', [
                        'name' => 'lastName',
                        'label' => 'Last name',
                        'class' => 'mdl-cell--12-col'
                    ])

                    @include('provider.partials.mdl.form.text.textfield', [
                        'name' => 'email',
                        'label' => 'Email',
                        'class' => 'mdl-cell--12-col',
                        'type' => 'email',
                    ])

                    @include('provider.partials.mdl.form.text.textfield', [
                        'name' => 'password',
                        'label' => 'Password',
                        'class' => 'mdl-cell--12-col',
                        'type' => 'password',
                    ])

                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary mdl-cell--12-col">
                        Create account
                    </button>
                </div>

                {!! Form::close() !!}

            </div>


        </main>

    </div>

@endsection