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
                        <h1>Permissions</h1>
                    </div>
                    @if(Cerberus::hasPermission('roles-permissions-manage'))
                        <div class="col-sm-4">
                            <div class="pull-right" style="margin:20px;">
                                <a href="{{ URL::route('permissions.create', array()) }}" class="btn btn-success">New
                                    Permission</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Permissions</div>
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
                        @foreach( $permissions as $permission )
                            <tr>
                                <td><a href="{{ URL::route('permissions.show', array('id' => $permission->id)) }}"
                                       class="btn btn-primary">Detail</a></td>
                                <td>{{ $permission->name }}</td>
                                <td>{{ $permission->display_name }}</td>
                                <td>{{ $permission->description }}</td>
                                <td>{{ date('F d, Y g:i A', strtotime($permission->created_at)) }}</td>
                                <td>
                                    @if(Cerberus::hasPermission('roles-permissions-manage'))
                                        <a href="{{ URL::route('permissions.edit', array('id' => $permission->id)) }}"
                                           class="btn btn-primary">Edit</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $permissions->appends(['action' => 'filter'])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
