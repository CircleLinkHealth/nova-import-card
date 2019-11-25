@extends('partials.providerUI')

@section('title', 'Work Schedule')
@section('activity', 'Work Schedule')

@section('content')
    <notifications></notifications>
    @include('errors.errors')

    <div class="container" style="margin-top: 2%">
        <nurse-schedule-calendar
                :auth-is-admin="{{$authIsAdmin}}">
        </nurse-schedule-calendar>
    </div>

@endsection
