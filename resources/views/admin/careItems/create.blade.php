@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => URL::route('admin.items.store', array()), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>New Item</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">New Item</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('pcp_id', 'PCP Id:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('pcp_id', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('items_parent', 'Items Parent:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('items_parent', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qid', 'qid:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qid', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('items_text', 'items_text:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('items_text', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.items.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Create Item', array('class' => 'btn btn-success')) !!}
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
