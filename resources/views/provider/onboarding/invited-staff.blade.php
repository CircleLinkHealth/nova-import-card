@extends('provider.layouts.onboarding')

@section('title', 'Welcome!')

@section('instructions', "Welcome! Let's create your user account.")

@section('module')

    <head>
        @push('styles')
            <style>
                .breadcrumb:last-child {
                    color: rgba(255, 255, 255, 0.7);
                }

                #step0 {
                    color: #039be5 !important;
                }

                nav {
                    display: none;
                }
            </style>
        @endpush
    </head>

    @include('provider.partials.errors.validation')

    {!! Form::open([
        'url' => route('get.onboarding.store.invited.user'),
        'method' => 'post',
        'id' => 'registration',
    ]) !!}

    <div class="row">
        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'first_name',
            'label' => 'First name',
            'class' => 'col s6',
            'value' => $user->first_name,
            'attributes' => [
                'required' => 'required',
            ]
        ])

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'last_name',
            'label' => 'Last name',
            'class' => 'col s6',
            'value' => $user->last_name,
            'attributes' => [
                'required' => 'required',
            ]
        ])
    </div>

    <div class="row">
        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'email',
            'label' => 'Email (Will also be the username)',
            'class' => 'col s12',
            'type' => 'email',
            'value' => $user->email,
            'attributes' => [
                'autocomplete' => 'new-email',
                'required' => 'required',
            ]
        ])
        <input type="hidden" value="{{$user->id}}" name="userId">
    </div>

    <div class="row">
        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'password',
            'label' => 'Password',
            'class' => 'col s12',
            'type' => 'password',
            'attributes' => [
                'autocomplete' => 'new-password',
                'required' => 'required',
            ]
        ])
    </div>

    <div class="row">
        <button class="btn blue waves-effect waves-light col s12" type="submit" name="submit">
            Create account
        </button>
    </div>

    {!! Form::close() !!}

@endsection