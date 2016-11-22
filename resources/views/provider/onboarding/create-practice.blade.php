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
            @include('provider.partials.mdl.form.checkbox', [
               'label' => 'Different for each location?',
               'name' => 'different-ehr-login-per-location',
               'value' => '1',
           ])
        </p>
    </div>

    <div class="row">
        <h6>
            Who should be notified for patient clinical issues?
        </h6>

        <p>
            @include('provider.partials.mdl.form.radio', [
                'id' => 'billing-provider',
                'label' => 'Patient billing provider.',
                'name' => 'clinical-contact',
                'value' => 'billing-provider',
            ])
        </p>

        <p>
            @include('provider.partials.mdl.form.radio', [
                'id' => 'instead-of-billing-provider',
                'label' => 'Someone else instead of billing provider.',
                'name' => 'clinical-contact',
                'value' => 'instead-of-billing-provider',
            ])
        </p>

        <p>
            @include('provider.partials.mdl.form.radio', [
                'id' => 'in-addition-to-billing-provider',
                'label' => 'Someone else in addition to the billing provider.',
                'name' => 'clinical-contact',
                'value' => 'in-addition-to-billing-provider',
            ])
        </p>


        <p class="right-align">
            @include('provider.partials.mdl.form.checkbox', [
               'label' => 'Different for each location?',
               'name' => 'different-clinical-contact-per-location',
               'value' => '1',
           ])
        </p>
    </div>

    <button class="btn blue waves-effect waves-light col s12"
            id="store-practice">
        Save practice
    </button>

    {!! Form::close() !!}

@endsection