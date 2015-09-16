@extends('app')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Item</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">View Item: {{ $item->name }}</div>
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
                                <td><div class="btn btn-orange btn-xs">{{ $item->msg_id }}</div></td>
                                <td>{{ $item->qtype }}</td>
                                <td>{{ $item->obs_key }}</td>
                                <td>{{ $item->meta_key }}</td>
                                <td>{{ $item->category }}</td>
                                <td><a href="{{ URL::route('admin.items.edit', array('id' => $item->items_id)) }}" class="btn btn-primary">Edit</a></td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Msg ID:</strong><br>
                            {{ $item->msg_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Item Type:</strong><br>
                            {{ $item->qtype }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Obs Key:</strong><br>
                            <p>{{ $item->obs_key }}</p>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Icon:</strong><br>
                            {{ $item->icon }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Category:</strong><br>
                            {{ $item->category }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Description:</strong><br>
                            {{ $item->description }}
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
