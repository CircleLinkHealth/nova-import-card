@extends('app')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @if (isset($error))
                    <div class="alert alert-danger">
                        <ul>
                            <li>{{ $error }}</li>
                        </ul>
                    </div>
                @endif

                @if (isset($success))
                    <div class="alert alert-success">
                        <ul>
                            <li>{{ $success }}</li>
                        </ul>
                    </div>
                @endif

                @if(Entrust::can('apikeys-manage'))
                    <div class="panel panel-default">
                        <div class="panel-heading">Create a new Api Key</div>
                        <div class="panel-body">


                            <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ URL::route('admin.apikeys.store', array()) }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                <div class="form-group">
                                    <label class="col-md-4 control-label">Client Name (eg. Android App Testing, Staging Site)</label>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" name="client_name" value="{{ old('client_name') }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-6 col-md-offset-4">
                                        <button type="submit" class="btn btn-primary">
                                            Create
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">All Api Keys</div>
                    <div class="panel-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @foreach( $apiKeys as $key )

                            <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ URL::route('admin.apikeys.destroy', array('id' => $key->id)) }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input name="_method" type="hidden" value="DELETE">

                                <div class="form-group">
                                    <div class="col-md-12">
                                        <p style="display: inline-block;">{{ $key->client_name }} => {{ $key->key }}</p>

                                        @if(Entrust::can('apikeys-manage'))
                                            <button type="submit" class="btn btn-primary pull-right">
                                                Delete Key
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
