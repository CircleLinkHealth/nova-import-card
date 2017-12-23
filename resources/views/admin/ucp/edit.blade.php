@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => URL::route('admin.ucp.update', array('id' => $ucp->ucp_id)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit User Care Plan</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit UCP Item: {{ $ucp->msg_id }}</div>
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
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{ URL::route('admin.ucp.show', array('id' => $ucp->ucp_id)) }}" class="btn btn-primary">Detail</a></td>
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
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('items_id', 'Items Id:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('items_id', $ucp->items_id, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('user_id', 'User Id:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('user_id', $ucp->user_id, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('meta_key', 'Meta Key:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('meta_key', $ucp->meta_key, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('meta_value', 'Meta Value:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('meta_value', $ucp->meta_value, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.ucp.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update UCP Item', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
