@extends('provider.layouts.onboarding')

@section('title', 'Create Staff')

@section('instructions', "Congratulations! You have successfully created a bunch of locations. Now add a bunch of staff members. Title: create-staff-users Step 4/4")

@section('module')

    @include('provider.partials.errors.validation')

    {!! Form::open([
        'url' => route('post.onboarding.store.staff'),
        'method' => 'post',
        'id' => 'create-staff',
    ]) !!}

    @include('provider.partials.mdl.form.text.textfield', [
        'name' => 'email',
        'label' => 'Email',
        'class' => 'col s6',
    ])

    <div class="input-field col s6">
        <select>
            <option value="" disabled selected>Choose a role</option>
            <option value="1">Medical Assistant</option>
            <option value="2">Specialist Doctor</option>
            <option value="3">Program Lead</option>
        </select>
        <label>Role</label>
    </div>

    <button class="btn blue waves-effect waves-light col s12"
            id="store-staff">
        Save Staff
    </button>

    {!! Form::close() !!}

@endsection
