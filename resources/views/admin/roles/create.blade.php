@extends('partials.adminUI')

@section('content')
    {!! Form::open(array('url' => URL::route('roles.store', array()), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>New Role</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">New Role</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('name', 'Role Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('name', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('display_name', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div id="summary" style="margin:20px 0px;">
                            <strong>Description:</strong>
                            <div class="form-group" id="description">
                                <div class="col-sm-12">{!! Form::textarea('description','',['class'=>'form-control', 'rows' => 4, 'cols' => 10]) !!}</div>
                            </div>
                        </div>



                        <h3>Permissions:</h3>
                        <br />
                        <div id="conditions">
                            @foreach( $permissions as $permission )
                                <div class="form-group condition" id="perm_{{ $permission }}">
                                    <div class="col-sm-5">{!! Form::label('Value', 'Permission: '.$permission->display_name, array('class' => '')) !!}</div>
                                    <div class="col-sm-1">
                                        {!! Form::checkbox('permissions[]', $permission->id, [], ['style' => '']) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('roles.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Create Role', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
