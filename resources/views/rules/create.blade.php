@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/rules/rules.js') }}"></script>
    {!! Form::open(array('url' => URL::route('admin.rules.store'), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Add New Rule</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('rule_name', 'Rule Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('rule_name', '', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div id="summary" style="margin:20px 0px;">
                            <strong>Summary:</strong>
                            <div class="form-group" id="summary">
                                <div class="col-sm-12">{!! Form::textarea('summary','',['class'=>'form-control', 'rows' => 4, 'cols' => 10]) !!}</div>
                            </div>
                        </div>

                        <div class="form-group" style="margin:20px 0px;">
                            <strong>Description:</strong>
                            <div class="form-group" id="rule_description">
                                <div class="col-sm-12">{!! Form::textarea('rule_description','',['class'=>'form-control', 'rows' => 2, 'cols' => 10]) !!}</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('type_id', 'Type:') !!}</div>
                            <div class="col-sm-4">{!! Form::text('type_id', 'ATT', ['class' => 'form-control', 'style' => 'width:90%;']) !!}</div>
                            <div class="col-sm-2">{!! Form::label('sort', 'Sort:') !!}</div>
                            <div class="col-sm-4">{!! Form::text('sort', '0', ['class' => 'form-control', 'style' => 'width:90%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-1">{!! Form::label('active', 'Active:') !!}</div>
                            <div class="col-sm-3">{!! Form::text('active', 'Y', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                            <div class="col-sm-1">{!! Form::label('approve', 'Approve:') !!}</div>
                            <div class="col-sm-3">{!! Form::text('approve', 'Y', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                            <div class="col-sm-1">{!! Form::label('archive', 'Archive:') !!}</div>
                            <div class="col-sm-3">{!! Form::text('archive', 'N', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <strong>Conditions:</strong>
                        <div id="conditions">
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::button('<span class="glyphicon glyphicon-plus-sign"></span> Condition', array('class' => 'btn btn-primary add-condition')) !!}
                                </div>
                            </div>
                        </div>

                        <strong>Actions:</strong>
                        <div id="actions">
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::button('<span class="glyphicon glyphicon-plus-sign"></span> Action', array('class' => 'btn btn-primary add-action')) !!}
                                </div>
                            </div>
                        </div>



                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::button('Cancel', array('class' => 'btn btn-danger')) !!}
                                    {!! Form::submit('Create Rule', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

        <div style="display:none;">
            <div id="jsconditions">
                <div class="form-group condition" id="cc*count">
                    <div class=" col-sm-1">{!! Form::label('Condition', 'Condition:', array('class' => '')) !!}</div>
                    <div class=" col-sm-3">{!! Form::select('c*condition', $conditions, '', ['class' => 'form-control select-picker c-condition', 'style' => 'width:120px;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::label('Operator', 'Operator:', array('class' => '')) !!}</div>
                    <div class=" col-sm-2">{!! Form::select('c*operator', $operators, '', ['class' => 'form-control select-picker c-operator']) !!}</div>
                    <div class=" col-sm-1">{!! Form::label('Value', 'Value:', array('class' => '')) !!}</div>
                    <div class=" col-sm-3">{!! Form::text('c*value', '', ['class' => 'form-control c-value', 'style' => 'width:120px;']) !!}{!! Form::checkbox('conditions[]', 'c*count', ['checked' => 'checked'], ['style' => 'display:none;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::button('<span class="glyphicon glyphicon-minus-sign"></span>', array('class' => 'btn btn-primary remove-condition', 'count' => 'c*count')) !!}</div>
                </div>
            </div>
            <div id="jsactions">
                <div class="form-group action" id="aa*count">
                    <div class=" col-sm-1">{!! Form::label('action', 'Action:') !!}</div>
                    <div class=" col-sm-3">{!! Form::select('a*action', $actions, '', ['class' => 'form-control select-picker a-condition', 'style' => 'width:120px;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::label('Operator', 'Operator:', array('class' => '')) !!}</div>
                    <div class=" col-sm-2">{!! Form::select('a*operator', $operators, '', ['class' => 'form-control select-picker a-operator', 'style' => 'width:100px;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::label('value', 'Value:') !!}</div>
                    <div class=" col-sm-3">{!! Form::text('a*value', '', ['class' => 'form-control a-value', 'style' => 'width:120px;']) !!}{!! Form::checkbox('actions[]', 'a*count', ['checked' => 'checked'], ['style' => 'display:none;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::button('<span class="glyphicon glyphicon-minus-sign"></span>', array('class' => 'btn btn-primary remove-action', 'count' => 'a*count')) !!}</div>
                </div>
            </div>
        </div>


    </div>
@stop
