@extends('partials.providerUI')

@section('title', 'Two Factor Authentication')
@section('activity', 'Two Factor Authentication')


@section('content')

    <div class="container container--menu">
        <div class="row">
            <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
                @if(auth()->user()->isAdmin() && !optional(auth()->user()->authyUser)->is_authy_enabled)
                    <div class="alert alert-danger">
                        Administrators are required to enable 2FA before logging in.
                    </div>
                @endif
                <user-account-settings :user="{{auth()->user()}}"></user-account-settings>
            </div>
        </div>
    </div>
@endsection