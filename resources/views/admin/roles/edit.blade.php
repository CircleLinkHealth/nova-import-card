@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/rules/rules.js') }}"></script>
    {!! Form::open(array('url' => URL::route('roles.update', array('id' => $role->id)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Role</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Role: {{ $role->name }}</div>
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
                                <td><a href="{{ URL::route('roles.show', array('id' => $role->id)) }}"
                                       class="btn btn-primary">{{ $role->id }} Detail</a></td>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->display_name }}</td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('name', 'Role Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('name', $role->name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('display_name', $role->display_name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div id="summary" style="margin:20px 0px;">
                            <strong>Description:</strong>
                            <div class="form-group" id="description">
                                <div class="col-sm-12">{!! Form::textarea('description',$role->description,['class'=>'form-control', 'rows' => 4, 'cols' => 10]) !!}</div>
                            </div>
                        </div>


                        <h3>Permissions:</h3>
                        <br/>
                        <div id="permissions">
                            @foreach( $permissions as $permission )
                                <div class="form-group permissions" id="perm_{{ $permission }}">
                                    <div class="col-sm-1">
                                        @if( in_array($permission->id, $role->perms()->pluck('id')->all()) )
                                            {!! Form::checkbox('permissions[]', $permission->id, ['checked' => "checked"], ['style' => '']) !!}
                                        @else
                                            {!! Form::checkbox('permissions[]', $permission->id, [], ['style' => '']) !!}
                                        @endif
                                    </div>
                                    <div class="col-sm-11">{!! Form::label('Value', ''.$permission->display_name, array('class' => '')) !!}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('roles.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Role', array('class' => 'btn btn-success')) !!}
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
