@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Permission</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">View Permission: {{ $permission->name }}</div>
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
                                <td>
                                    @if(Cerberus::can('roles-permissions-manage'))
                                        <a href="{{ URL::route('admin.permissions.edit', array('id' => $permission->id)) }}" class="btn btn-primary">Edit</a>
                                    @endif
                                </td>
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
                            {{ date('F d, Y g:i A', strtotime($permission->created_at)) }}
                        </div>

                        <h3>Roles:</h3>
                        <div id="conditions">
                            @foreach( $permission->roles as $role )
                                <div class="form-group condition" id="perm_{{ $role }}">
                                    <div class="col-sm-12">
                                            {!! $role->display_name !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
