@extends('partials.providerUI')

@section('title', 'See All Notifications')
@section('activity', 'See All Notifications')

@section('content')
    <pusher-see-all-notifications :notifications="{{json_encode($notifications)}}"></pusher-see-all-notifications>
@endsection

