@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Configure Redox API variables</div>
                    <div class="panel-body">

                        @include('errors.errors')

                        <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ action('qliqSOFT\ConfigController@update') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="PUT">

                            <div class="form-group">
                                <label class="col-md-4 control-label">Api Key</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="qliqsoft_api_key" value="{{ $apiKey }}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Api URL</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="qliqsoft_api_url" value="{{ $apiUrl }}" required>
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
    </div>
@endsection
