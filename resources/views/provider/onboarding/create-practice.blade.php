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

    <div class="row">
        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'name',
            'label' => 'Organization Name',
            'class' => 'col s12',
        ])
    </div>

    <div class="row">
        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'numberOfLocations',
            'label' => 'How many locations?',
            'class' => 'col s6',
            'type'  => 'number',
        ])

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'tax-id',
            'label' => 'Tax Id',
            'class' => 'col s6',
        ])
    </div>

    <div class="row">
        <h6>
            Please provide login information for your EHR system.
        </h6>

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'ehr-login',
            'label' => 'EHR Login',
            'class' => 'col s6',
        ])

        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'ehr-password',
            'label' => 'EHR Password',
            'class' => 'col s6',
            'type' => 'password',
            'attributes' => [
                'autocomplete' => 'new-password',
                'required' => 'required',
            ]
        ])

        <p class="right-align">
            <input type="checkbox" id="test5"/>
            <label for="test5">Same for all locations?</label>
        </p>
    </div>

    <div class="row">
        <h6>
            Who should be notified for patient issues?
        </h6>

        <p>
            <input name="group1" type="radio" id="test1"/>
            <label for="test1">Patient's billing provider.</label>
        </p>
        <p>
            <input name="group1" type="radio" id="test2"/>
            <label for="test2">Someone else instead of billing provider.</label>
        </p>
        <p>
            <input name="group1" type="radio" id="test3"/>
            <label for="test3">Someone else in addition to the billing provider.</label>
        </p>

        <p class="right-align">
            <input type="checkbox" id="test5"/>
            <label for="test5">Same for all locations?</label>
        </p>
    </div>

    <button class="btn blue waves-effect waves-light col s12"
            id="store-practice">
        Save practice
    </button>

    {!! Form::close() !!}

@endsection