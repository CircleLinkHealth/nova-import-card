@extends('provider.layouts.default')

@section('title', 'Create Program Lead')

<head>
    <style>
        .onboarding-user-card > .mdl-card__title {
            color: #fff;
            height: 190px;
            background: url({{asset('/img/clh_logo.svg')}}) center / contain;
            background-repeat: no-repeat;
            padding: 0;
            margin: 0;
        }
    </style>
</head>

@section('content')
    <div class="mdl-layout mdl-js-layout">
        <div class="mdl-grid full-height">
            @include('errors.errors')

            <div class="v-center">

                <div class="mdl-card mdl-shadow--1dp onboarding-user-card mdl-cell mdl-cell--6-col">

                    <div class="mdl-card__title"></div>

                    <div class="mdl-cell--12-col">
                        <h5 class="mdl-typography--text-center">
                            Welcome to CarePlan Manager!
                        </h5>
                        <div class="mdl-layout-spacer" style="height: 2%;"></div>
                        <h6>
                            Let's get started by creating an account manager? Page name is create-program-lead.
                        </h6>
                    </div>

                    {!! Form::open([
                        'url' => route('post.store.program.lead.user'),
                        'method' => 'post',
                        'id' => 'registration',
                    ]) !!}


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
                        'attributes' => [
                            'autocomplete' => 'new-email',
                        ]
                    ])

                    @include('provider.partials.mdl.form.text.textfield', [
                        'name' => 'password',
                        'label' => 'Password',
                        'class' => 'mdl-cell--12-col',
                        'type' => 'password',
                        'attributes' => [
                            'autocomplete' => 'new-password',
                        ]
                    ])

                    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary mdl-cell--12-col">
                        Create account
                    </button>

                    {!! Form::close() !!}

                </div>

            </div>

        </div>
    </div>
@endsection