@extends('provider.layouts.dashboard')

@section('title', 'Manage Staff Members')

@section('module')

    <div class="container">
        @include('provider.partials.modules.manage-staff', [
        'submitLabel' => 'Save',
        'postUrl' => route('provider.dashboard.store.staff', ['practiceSlug' => $practiceSlug]),
    ])
    </div>

@endsection