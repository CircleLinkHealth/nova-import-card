@extends('app')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View User Care Plan</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">View User Care Plan: {{ $ucp->name }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>msg_id</strong></td>
                                <td><strong>qtype</strong></td>
                                <td><strong>obs_key</strong></td>
                                <td><strong>icon</strong></td>
                                <td><strong>category</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><div class="btn btn-orange btn-xs">{{ $ucp->msg_id }}</div></td>
                                <td>{{ $ucp->qtype }}</td>
                                <td>{{ $ucp->obs_key }}</td>
                                <td>{{ $ucp->meta_key }}</td>
                                <td>{{ $ucp->category }}</td>
                                <td><a href="{{ URL::route('admin.ucp.edit', array('id' => $ucp->ucp_id)) }}" class="btn btn-primary">Edit</a></td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Msg ID:</strong><br>
                            {{ $ucp->msg_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>User Care Plan Type:</strong><br>
                            {{ $ucp->qtype }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Obs Key:</strong><br>
                            <p>{{ $ucp->obs_key }}</p>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Icon:</strong><br>
                            {{ $ucp->icon }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Category:</strong><br>
                            {{ $ucp->category }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Description:</strong><br>
                            {{ $ucp->description }}
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
