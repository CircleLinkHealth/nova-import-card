@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Rule Detail</div>
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

                        <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ action('RulesController@store') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                            <div class="form-group">
                                <label class="col-md-4 control-label">Name</label>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                </div>
                            </div>


                            <div class="form-group">
                                <label class="col-md-4 control-label">Condition</label>
                                <div class="col-md-6">
                                    <select name="parent_id">
                                        @foreach( $conditions as $condition )
                                            <option value="{{ $condition->id }}">{{ $condition->condition_description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Actions</label>
                                <div class="col-md-6">
                                    <select name="parent_id">
                                        @foreach( $actions as $action )
                                            <option value="{{ $action->id }}">{{ $action->action_description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Operator</label>
                                <div class="col-md-6">
                                    <select name="parent_id">
                                        @foreach( $operators as $oper )
                                            <option value="{{ $oper->id }}">{{ $oper->operator_description }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Add Location
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
