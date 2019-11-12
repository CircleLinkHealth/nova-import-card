@extends('partials.providerUI')

@section('title', 'Work Schedule')
@section('activity', 'Work Schedule')

@section('content')
    <notifications></notifications>
    @include('errors.errors')

    <div class="container">
        <nurse-schedule-calendar
                :auth-is-admin="{{$authIsAdmin}}">
        </nurse-schedule-calendar>
    </div>

@endsection
