@extends('partials.adminUI')

@section('content')
    {!! Form::open(array('url' => route('admin.permissions.update', array('id' => $permission->id)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Permission</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Permission: {{ $permission->name }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td><strong>id</strong></td>
                                    <td><strong>name</strong></td>
                                    <td><strong>display name</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{ route('admin.permissions.show', array('id' => $permission->id)) }}" class="btn btn-primary">{{ $permission->id }} Detail</a></td>
                                    <td>{{ $permission->name }}</td>
                                    <td>{{ $permission->display_name }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('name', 'Permission Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('name', $permission->name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('display_name', $permission->display_name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div id="summary" style="margin:20px 0px;">
                            <strong>Description:</strong>
                            <div class="form-group" id="description">
                                <div class="col-sm-12">{!! Form::textarea('description',$permission->description,['class'=>'form-control', 'rows' => 4, 'cols' => 10]) !!}</div>
                            </div>
                        </div>



                        <h3>Roles:</h3>
                        <br />
                        <div id="conditions">
                        @foreach( $roles as $role )
                            <div class="form-group condition" id="role_{{ $role }}">
                                <div class="col-sm-1">
                                @if( in_array($role->id, $permissionRoles) )
                                    {!! Form::checkbox('roles[]', $role->id, ['checked' => "checked"], ['style' => '']) !!}
                                @else
                                    {!! Form::checkbox('roles[]', $role->id, [], ['style' => '']) !!}
                                @endif
                                </div>
                                <div class="col-sm-11">{!! Form::label('Value', ''.$role->display_name, array('class' => '')) !!}</div>
                            </div>
                        @endforeach
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ route('admin.permissions.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Permission', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
