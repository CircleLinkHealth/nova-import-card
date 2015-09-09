@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Configure Redox API variables</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ action('Redox\ConfigController@store') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group">
                                <label class="col-md-4 control-label">Api Key</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="redox_api_key" value="{{ old('redox_api_key') }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Api Secret</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="redox_api_secret" value="{{ old('redox_api_secret') }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">App Verification Token</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="redox_app_verification_token" value="{{ old('redox_app_verification_token') }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Save Settings
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
