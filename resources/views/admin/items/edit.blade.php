@extends('app')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('admin.items.update', array('id' => $item->items_id)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Item</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Item: {{ $item->msg_id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <td></td>
                                    <td><strong>msg_id</strong></td>
                                    <td><strong>qtype</strong></td>
                                    <td><strong>obs_key</strong></td>
                                    <td><strong>icon</strong></td>
                                    <td><strong>category</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{ URL::route('admin.items.show', array('id' => $item->items_id)) }}" class="btn btn-primary">{{ $item->items_id }} Detail</a></td>
                                    <td><div class="btn btn-orange btn-xs">{{ $item->msg_id }}</div></td>
                                    <td>{{ $item->qtype }}</td>
                                    <td>{{ $item->obs_key }}</td>
                                    <td>{{ $item->meta_key }}</td>
                                    <td>{{ $item->category }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('msg_id', 'Msg ID:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('msg_id', $item->msg_id, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qtype', 'qtype:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qtype', $item->qtype, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('obs_key', 'Obs Key:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('obs_key', $item->obs_key, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('icon', 'Icon:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('icon', $item->icon, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('category', 'Category:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('category', $item->category, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div id="summary" style="margin:20px 0px;">
                            <strong>Description:</strong>
                            <div class="form-group" id="description">
                                <div class="col-sm-12">{!! Form::textarea('description',$item->description,['class'=>'form-control', 'rows' => 4, 'cols' => 10]) !!}</div>
                            </div>
                        </div>


                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.items.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Item', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
