@extends('cpm-admin::partials.adminUI')
@push('styles')
<style>
    .feature-toggle-content {
        margin-left: 10rem;
        margin-right: 10rem;
    }
</style>
@endpush
@section('content')
<div class="feature-toggle-content">
    @yield('feature-toggle-content')
</div>
@endsection