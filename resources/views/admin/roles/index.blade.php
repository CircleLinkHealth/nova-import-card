@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Roles</h1>
                    </div>
                    @if(Cerberus::can('roles-manage'))
                        <div class="col-sm-4">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ URL::route('roles.create', array()) }}" class="btn btn-success">New Role</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Roles</div>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td></td>
                            <td><strong>name</strong></td>
                            <td><strong>display name</strong></td>
                            <td><strong>description</strong></td>
                            <td><strong>created at</strong></td>
                            <td></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $roles as $role )
                            <tr>
                                <td><a href="{{ URL::route('roles.show', array('id' => $role->id)) }}"
                                       class="btn btn-primary">Detail</a></td>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->display_name }}</td>
                                <td>{{ $role->description }}</td>
                                <td>{{ date('F d, Y g:i A', strtotime($role->created_at)) }}</td>
                                <td>
                                    @if(Cerberus::can('roles-manage'))
                                        <a href="{{ URL::route('roles.edit', array('id' => $role->id)) }}"
                                           class="btn btn-primary">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $roles->appends(['action' => 'filter'])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
