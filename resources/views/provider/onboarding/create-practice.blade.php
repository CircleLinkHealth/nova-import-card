@extends('provider.layouts.onboarding')

@section('title', 'Create Practice')

@section('instructions', "What is the name and description of the practice? Do we need description Raph? Title: create-practice")

@section('module')

    @include('errors.errors')

    {!! Form::open([
        'url' => route('post.onboarding.store.practice'),
        'method' => 'post',
        'id' => 'create-practice',
    ]) !!}

    @include('provider.partials.mdl.form.text.textfield', [
        'name' => 'name',
        'label' => 'Name',
        'class' => 'mdl-cell--12-col',
    ])

    @include('provider.partials.mdl.form.text.textfield', [
        'name' => 'description',
        'label' => 'Description',
        'class' => 'mdl-cell--12-col',
    ])

    @include('provider.partials.mdl.form.text.textfield', [
        'name' => 'numberOfLocations',
        'label' => 'How many locations?',
        'class' => 'mdl-cell--12-col',
        'type'  => 'number',
    ])

    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--primary mdl-cell--12-col"
            id="create-practice">
        Create practice
    </button>

    {!! Form::close() !!}

@endsection