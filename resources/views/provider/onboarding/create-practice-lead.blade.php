@extends('provider.layouts.onboarding')

@section('title', 'Lead')

@section('instructions', "Welcome! Let's start by creating your implementation lead.")

@section('module')

    <head>
        <style>
            .breadcrumb:last-child {
                color: rgba(255, 255, 255, 0.7);
            }

            #step0 {
                color: #fff;
            }
        </style>
    </head>

    @include('provider.partials.errors.validation')

    {!! Form::open([
        'url' => route('post.onboarding.store.program.lead.user'),
        'method' => 'post',
        'id' => 'registration',
    ]) !!}

    <div class="row">
        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'firstName',
            'label' => 'First name',
            'class' => 'col s6',
            'attributes' => [
                'required' => 'required',
            ]
        ])

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'lastName',
            'label' => 'Last name',
            'class' => 'col s6',
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
            'attributes' => [
                'autocomplete' => 'new-email',
                'required' => 'required',
            ]
        ])
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

    <button class="btn blue waves-effect waves-light col s12" type="submit" name="submit" id="update-practice">
        Save practice lead
    </button>

    {!! Form::close() !!}

@endsection