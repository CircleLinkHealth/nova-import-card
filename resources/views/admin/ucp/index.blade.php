@extends('app')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
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
                        <h1>User Care Plans</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.ucp.create', array()) }}" class="btn btn-success">New User Care Plan</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All User Care Plans</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td></td>
                                <td><strong>items_id</strong></td>
                                <td><strong>user_id</strong></td>
                                <td><strong>meta_key</strong></td>
                                <td><strong>meta_value</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $ucps as $ucp )
                                <tr>
                                    <td><a href="{{ URL::route('admin.ucp.show', array('id' => $ucp->ucp_id)) }}" class="btn btn-primary">Detail</a></td>
                                    <td><div class="btn btn-orange btn-xs">{{ $ucp->items_id }}</div></td>
                                    <td>{{ $ucp->user_id }}</td>
                                    <td>{{ $ucp->meta_key }}</td>
                                    <td>{{ $ucp->meta_value }}</td>
                                    <td><a href="{{ URL::route('admin.ucp.edit', array('id' => $ucp->ucp_id)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.ucp.destroy', array('id' => $ucp->ucp_id)) }}" class="btn btn-warning">Remove</a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {!! $ucps->appends(['action' => 'filter'])->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
