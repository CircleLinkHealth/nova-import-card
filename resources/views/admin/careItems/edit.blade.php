@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
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
                                    <td><strong>pcp</strong></td>
                                    <td><strong>items_parent</strong></td>
                                    <td><strong>qid</strong></td>
                                    <td><strong>items_text</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{ URL::route('admin.items.show', array('id' => $item->items_id)) }}" class="btn btn-primary">Detail</a></td>
                                    <td>
                                        @if($item->pcp)
                                            <a href="{{ URL::route('admin.items.show', array('id' => $item->items_id)) }}" class="btn btn-orange btn-xs">{{ $item->pcp->section_text }}</a>
                                        @else
                                            {{ $item->pcp_id }}
                                        @endif
                                    </td>
                                    <td>{{ $item->items_parent }}</td>
                                    <td><a href="{{ URL::route('admin.questions.show', array('id' => $item->qid)) }}" class="btn btn-orange btn-xs">{{ $item->qid }}</a></td>
                                    <td>{{ $item->items_text }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('pcp_id', 'PCP Id:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('pcp_id', $item->pcp_id, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('items_parent', 'Items Parent:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('items_parent', $item->items_parent, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qid', 'qid:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qid', $item->qid, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('items_text', 'items_text:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('items_text', $item->items_text, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.items.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Item', array('class' => 'btn btn-success')) !!}
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
