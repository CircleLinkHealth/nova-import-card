@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
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
                                <td><strong>items_id</strong></td>
                                <td><strong>user_id</strong></td>
                                <td><strong>meta_key</strong></td>
                                <td><strong>meta_value</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    @if($ucp->item)
                                        <a href="{{ URL::route('admin.items.show', array('id' => $ucp->items_id)) }}" class="btn btn-orange btn-xs">{{ $ucp->item->items_text }}</a>
                                    @else
                                        {{ $ucp->items_id }}
                                    @endif
                                </td>
                                <td>{{ $ucp->user_id }}</td>
                                <td>{{ $ucp->meta_key }}</td>
                                <td><strong>{{ $ucp->meta_value }}</strong></td>
                                <td><a href="{{ URL::route('admin.ucp.edit', array('id' => $ucp->ucp_id)) }}" class="btn btn-primary">Edit</a> <a href="{{ URL::route('admin.ucp.destroy', array('id' => $ucp->ucp_id)) }}" class="btn btn-warning">Remove</a></td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Items Id:</strong><br>
                            {{ $ucp->items_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>User Id:</strong><br>
                            {{ $ucp->user_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Meta Key:</strong><br>
                            <p>{{ $ucp->meta_key }}</p>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Meta Value:</strong><br>
                            {{ $ucp->meta_value }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
