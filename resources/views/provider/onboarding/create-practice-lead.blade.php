@extends('provider.layouts.onboarding')

@section('title', 'Lead')

@section('instructions')
    Welcome! Let's start by creating <u>your implementation lead</u>.
@endsection

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
            </style>
        @endpush
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

    <div class="right-align">
        @include('provider.partials.mdl.form.checkbox', [
            'label' => 'Provider or Clinical Staff',
            'name' => 'countCcmTime',
            'value' => '1',
            'class' => 'col s12',
        ])
    </div>

    <div class="row">
        @include('provider.partials.mdl.form.text.textfield', [
            'name' => 'email',
            'label' => 'Email (Will also be the username)',
            'class' => 'col s12',
            'type' => 'email',
            'value' => isset($invite) ? $invite->email : '',
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

    @if(isset($invite))
        <input type="hidden" name="code" value="{{$invite->code}}">
    @endif

    <div class="row">
        <button class="btn blue waves-effect waves-light col s12" type="submit" name="submit" id="update-practice">
            Next
        </button>
    </div>

    {!! Form::close() !!}

@endsection