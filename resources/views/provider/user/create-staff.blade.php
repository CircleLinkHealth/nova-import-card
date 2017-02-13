@extends('provider.layouts.dashboard')

@section('title', 'Manage Staff Members')

@section('module')

    <div class="container">
        @include('provider.partials.modules.create-users', [
        'submitLabel' => 'Save',
        'postUrl' => route('post.onboarding.store.staff', ['practiceSlug' => $practiceSlug])
    ])
    </div>

@endsection