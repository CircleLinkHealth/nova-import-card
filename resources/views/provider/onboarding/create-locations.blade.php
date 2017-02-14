@extends('provider.layouts.onboarding')

@section('title', 'Create locations')

@section('instructions', "Almost done! Let's <u>setup locations</u>.")

@section('module')

    <head>
        <style>
            .breadcrumb:last-child {
                color: rgba(255, 255, 255, 0.7);
            }

            #step2 {
                color: #039be5 !important;
            }
        </style>
    </head>

    @include('provider.partials.modules.manage-locations', [
    'submitLabel' => 'Next',
    'postUrl' => route('post.onboarding.store.locations', ['lead_id' => $leadId])
])
@endsection


