@extends('partials.providerUI')

@section('title', 'Work Schedule')
@section('activity', 'Work Schedule')

@section('content')
    <notifications></notifications>
    @include('core::partials.errors.errors')

    <div class="container" style="margin-top: 2%">
        <nurse-schedule-calendar
                :auth-data="{{json_encode($authData)}}"
                :today="{{json_encode($today)}}">
        </nurse-schedule-calendar>
    </div>

@endsection
