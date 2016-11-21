@extends('provider.layouts.onboarding')

@section('title', 'Create Practice')

@section('instructions', "Congratulations! You have successfully created a lead user account. What is the name of the practice? How should we word this Raph? Title: create-practice. Step 2/4")

@section('module')

        @include('provider.partials.errors.validation')

        {!! Form::open([
            'url' => route('post.onboarding.store.practice'),
            'method' => 'post',
            'id' => 'create-practice',
        ]) !!}

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'name',
            'label' => 'Organization Name',
            'class' => 'col s12',
        ])

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'numberOfLocations',
            'label' => 'How many locations?',
            'class' => 'col s12',
            'type'  => 'number',
        ])

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'tax-id',
            'label' => 'Tax Id',
            'class' => 'col s12',
        ])

        <button class="btn blue waves-effect waves-light col s12"
                id="store-practice">
                Save practice
        </button>

    {!! Form::close() !!}

@endsection