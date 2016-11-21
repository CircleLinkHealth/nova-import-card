@extends('provider.layouts.onboarding')

@section('title', 'Create Program Lead')

@section('instructions', "Let's start by creating an account manager? Page name is create-practice-lead. Step 1/4")

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
            'class' => 'col s12'
        ])

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'lastName',
            'label' => 'Last name',
            'class' => 'col s12'
        ])

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'email',
            'label' => 'Email',
            'class' => 'col s12',
            'type' => 'email',
            'attributes' => [
                'autocomplete' => 'new-email',
            ]
        ])

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'password',
            'label' => 'Password',
            'class' => 'col s12',
            'type' => 'password',
            'attributes' => [
                'autocomplete' => 'new-password',
            ]
        ])

        <button class="btn blue waves-effect waves-light col s12" type="submit" name="submit" id="update-practice">
                Create program lead
        </button>

    {!! Form::close() !!}

@endsection