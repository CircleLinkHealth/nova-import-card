@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">PERMISSION {{ $permission->name }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>name</strong></td>
                                <td><strong>display name</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $permission->name }}</td>
                                <td>{{ $permission->display_name }}</td>
                                <td><a href="{{ url('admin/permissions/'.$permission->id.'/edit') }}" class="btn btn-primary">Edit</a></td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Permission Name:</strong><br>
                            {{ $permission->name }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Display Name:</strong><br>
                            {{ $permission->display_name }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Description:</strong><br>
                            <p>{{ $permission->description }}</p>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Created:</strong><br>
                            {{ $permission->created_at }}
                        </div>

                        <h3>Roles:</h3>
                        <div id="conditions">
                            @foreach( $permission->roles as $role )
                                <div class="form-group condition" id="perm_{{ $role }}">
                                    <div class="col-sm-1">
                                            {!! Form::checkbox('roles[]', $role->id, ['checked' => "checked", 'disabled' => "disabled"], ['style' => '']) !!}
                                    </div>
                                    <div class="col-sm-11">{!! Form::label('Value', ''.$role->display_name, array('class' => '')) !!}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
