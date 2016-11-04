@extends('provider.layouts.onboarding')

@section('title', 'Create Program Lead')

@section('instructions', "Let's start by creating an account manager? Page name is create-program-lead.")

@section('module')

    @include('errors.errors')

    {!! Form::open([
        'url' => route('post.onboarding.store.program.lead.user'),
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

    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary mdl-cell--12-col"
            id="update-practice">
        Create program lead
    </button>

    {!! Form::close() !!}

@endsection