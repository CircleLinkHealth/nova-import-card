@extends('app')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
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
                    <div class="panel-heading">Edit User Care Plan: {{ $ucp->msg_id }}</div>
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
                                    <td><a href="{{ URL::route('admin.ucp.show', array('id' => $ucp->ucp_id)) }}" class="btn btn-primary">{{ $ucp->ucp_id }} Detail</a></td>
                                    <td><div class="btn btn-orange btn-xs">{{ $ucp->msg_id }}</div></td>
                                    <td>{{ $ucp->qtype }}</td>
                                    <td>{{ $ucp->obs_key }}</td>
                                    <td>{{ $ucp->meta_key }}</td>
                                    <td>{{ $ucp->category }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('msg_id', 'Msg ID:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('msg_id', $ucp->msg_id, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('qtype', 'qtype:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('qtype', $ucp->qtype, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('obs_key', 'Obs Key:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('obs_key', $ucp->obs_key, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('icon', 'Icon:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('icon', $ucp->icon, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('category', 'Category:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('category', $ucp->category, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div id="summary" style="margin:20px 0px;">
                            <strong>Description:</strong>
                            <div class="form-group" id="description">
                                <div class="col-sm-12">{!! Form::textarea('description',$ucp->description,['class'=>'form-control', 'rows' => 4, 'cols' => 10]) !!}</div>
                            </div>
                        </div>


                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.ucp.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update User Care Plan', array('class' => 'btn btn-success')) !!}
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
