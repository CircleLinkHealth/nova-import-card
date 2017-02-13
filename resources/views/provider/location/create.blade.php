@extends('provider.layouts.dashboard')

@section('title', 'Manage Locations')

@section('module')

    <div class="container">
        @include('provider.partials.modules.manage-locations', [
        'submitLabel' => 'Save',
        'postUrl' => route('post.onboarding.store.locations', ['lead_id' => $leadId])
    ])
    </div>

@endsection