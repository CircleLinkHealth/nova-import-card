@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="row" style="margin:20px auto;">
                    <div class="col-sm-12">
                        <div class="pull-right">
                            <a href="{{ url('admin/permissions/create') }}" class="btn btn-success">New Permission</a>
                        </div>
                    </div>
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
                                <td><a href="{{ url('admin/permissions/'.$permission->id.'') }}" class="btn btn-primary">Detail</a></td>
                                <td>{{ $permission->name }}</td>
                                <td>{{ $permission->display_name }}</td>
                                <td>{{ $permission->description }}</td>
                                <td>{{ $permission->created_at }}</td>
                                <td><a href="{{ url('admin/permissions/'.$permission->id.'/edit') }}" class="btn btn-primary">Edit</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
