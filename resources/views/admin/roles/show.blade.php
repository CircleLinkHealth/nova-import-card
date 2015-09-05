@extends('app')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/rules/rules.js') }}"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">ROLE {{ $role->name }}</div>
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
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->display_name }}</td>
                                <td><a href="{{ url('admin/roles/'.$role->id.'/edit') }}" class="btn btn-primary">Edit</a></td>
                            </tr>
                            </tbody>
                        </table>

                        <h3>Permissions:</h3>
                        <div id="permissions">
                            @foreach( $role->perms as $permission )
                                <div class="form-group condition" id="perm_{{ $permission }}">
                                    <div class="col-sm-1">
                                        {!! Form::checkbox('permissions[]', $permission->id, ['checked' => "checked", 'disabled' => "disabled"], ['style' => '']) !!}
                                    </div>
                                    <div class="col-sm-11">{!! Form::label('Value', ''.$permission->display_name, array('class' => '')) !!}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Role Name:</strong><br>
                            {{ $role->name }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Display Name:</strong><br>
                            {{ $role->display_name }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Description:</strong><br>
                            <p>{{ $role->description }}</p>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Created:</strong><br>
                            {{ $role->created_at }}
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
