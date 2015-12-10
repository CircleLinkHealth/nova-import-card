@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Configure Redox API variables</div>
                    <div class="panel-body">

                        @include('errors.errors')

                        <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ action('Redox\ConfigController@update') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="PUT">

                            <div class="form-group">
                                <label class="col-md-4 control-label">Api Key</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="redox_api_key" value="{{ $apiKey }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Api Secret</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="redox_api_secret" value="{{ $apiSecret }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">App Verification Token</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="redox_app_verification_token" value="{{ $appVerifToken }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Update Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Access Tokens in DB
                    </div>
                    <div class="panel-body">
                        @if(!empty($accessTokens))

                            @foreach($accessTokens as $accessToken)
                                <div class="form-group">
                                    <label class="col-md-4 control-label">
                                        {{ $accessToken['meta_key'] }}
                                    </label>
                                    <div class="">
                                        {{ $accessToken['meta_value'] }}
                                    </div>
                                </div>
                            @endforeach

                            @else

                            No access tokens found in db.

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
