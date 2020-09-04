@extends('partials.providerUI')

@section('title', 'Perform 2FA')
@section('activity', 'Perform 2FA')


@section('content')
    <div class="container container--menu">
        <div class="row">
            <div class="col-xs-12 col-sm-8 col-md-4 col-sm-offset-2 col-md-offset-4">
                <authy-perform-2fa :authy-user="{{$authyUser}}" redirect-to="{{$redirectTo}}"></authy-perform-2fa>
            </div>
        </div>
    </div>
@endsection
