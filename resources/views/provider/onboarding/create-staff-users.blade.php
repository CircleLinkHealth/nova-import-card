@extends('provider.layouts.onboarding')

@section('title', 'Create Staff')

@section('instructions', "One more! <br>Let's <u>create your team</u>.")

@section('module')

    <head>
        <style>
            .breadcrumb:last-child {
                color: rgba(255, 255, 255, 0.7);
            }

            #step3 {
                color: #039be5 !important;
            }
        </style>
    </head>

    @include('provider.partials.modules.create-users', [
        'submitLabel' => 'Next',
        'postUrl' => route('post.onboarding.store.staff', ['practiceSlug' => $practiceSlug])
    ])

@endsection