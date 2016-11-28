@extends('provider.layouts.onboarding')

@section('title', 'Create Practice')

@section('instructions', "Let's <u>create your organization</u>.")

@section('module')

    <head>
        <style>
            .breadcrumb:last-child {
                color: rgba(255, 255, 255, 0.7);
            }

            #step1 {
                color: #039be5 !important;
            }
        </style>
    </head>

    <div id="create-practice-component">
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
                'attributes' => [
                    'required' => 'required',
                ]
            ])
        </div>

        <div class="row">
            @include('provider.partials.mdl.form.text.textfield', [
                'name' => 'tax-id',
                'label' => 'Federal Tax ID#',
                'class' => 'col s12',
                'attributes' => [
                    'required' => 'required',
                ]
            ])
        </div>

        <div class="row">
            <button class="btn blue waves-effect waves-light col s12"
                    id="store-practice">
                Next
            </button>
        </div>

        {!! Form::close() !!}
    </div>
@endsection