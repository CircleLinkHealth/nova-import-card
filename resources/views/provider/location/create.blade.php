@extends('provider.layouts.dashboard')

@section('title', 'Manage Locations')

@section('module')

    <div class="container">
        @include('provider.partials.modules.manage-locations', [
        'submitLabel' => 'Finish/Save',
        'postUrl' => route('provider.dashboard.store.locations', ['practiceSlug' => $practiceSlug]),
    ])
    </div>

@endsection