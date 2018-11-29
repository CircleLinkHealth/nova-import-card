@extends('partials.providerUI')

@section('title', 'Two Factor Authentication')
@section('activity', 'Two Factor Authentication')


@section('content')

    <div class="container container--menu">
        <div class="row">
            <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
                <user-account-settings :user="{{auth()->user()}}"></user-account-settings>
            </div>
        </div>
    </div>
@endsection