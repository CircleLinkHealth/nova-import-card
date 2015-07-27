@extends('app')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/rules/rules.js') }}"></script>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">EDIT {{ $rule->rule_name }}</div>
                    <div class="panel-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>id</strong></td>
                                <td><strong>rule_name</strong></td>
                                <td><strong>rule_description</strong></td>
                                <td><strong>active</strong></td>
                                <td><strong>type_id</strong></td>
                                <td><strong>effective_date</strong></td>
                                <td><strong>expiration_date</strong></td>
                                <td><strong>active</strong></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><a href="{{ url('rules/'.$rule->id.'') }}" class="btn btn-success">{{ $rule->id }} Detail</a></td>
                                <td>{{ $rule->rule_name }}</td>
                                <td>{{ $rule->rule_description }}</td>
                                <td>{{ $rule->active }}</td>
                                <td>{{ $rule->type_id }}</td>
                                <td>{{ $rule->effective_date }}</td>
                                <td>{{ $rule->expiration_date }}</td>
                                <td>{{ $rule->active }}</td>
                            </tr>
                            </tbody>
                        </table>
                        <strong>Summary:</strong>
                        <p>{{ $rule->summary }}</p>

                        {!! Form::open(array('url' => '/rules/'.$rule->id, 'class' => 'form-horizontal')) !!}
                        <strong>Conditions:</strong>
                        <div id="conditions">
                        @foreach( $rule->intrConditions as $intrCondition )
                            <div class="form-group">
                                <div class=" col-sm-1">{!! Form::label('Condition', 'Condition:', array('class' => '')) !!}</div>
                                <div class=" col-sm-3">{!! Form::select('condition', $conditions, $intrCondition->condition->id, ['class' => 'form-control select-picker', 'style' => 'width:120px;']) !!}</div>
                                <div class=" col-sm-1">{!! Form::label('Operator', 'Operator:', array('class' => '')) !!}</div>
                                <div class=" col-sm-2">{!! Form::select('operator', $operators, $intrCondition->operator->id, ['class' => 'form-control select-picker', 'style' => 'width:100px;']) !!}</div>
                                <div class=" col-sm-1">{!! Form::label('Value', 'Value:', array('class' => '')) !!}</div>
                                <div class=" col-sm-3">{!! Form::text('value', $intrCondition->value, ['class' => 'form-control', 'style' => 'width:120px;']) !!}</div>
                                <div class=" col-sm-1">{!! Form::button('<span class="glyphicon glyphicon-minus-sign"></span>', array('class' => 'btn btn-primary remove-condition')) !!}</div>
                            </div>
                        @endforeach
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
                        @foreach( $rule->intrActions as $intrAction )
                            <div class="form-group">
                                <div class=" col-sm-1">{!! Form::label('action', 'Action:') !!}</div>
                                <div class=" col-sm-3">{!! Form::select('action', $actions, $intrAction->action->id, ['class' => 'form-control select-picker', 'style' => 'width:120px;']) !!}</div>
                                <div class=" col-sm-1">{!! Form::label('Operator', 'Operator:', array('class' => '')) !!}</div>
                                <div class=" col-sm-2">{!! Form::select('operator', $operators, $intrCondition->operator->id, ['class' => 'form-control select-picker', 'style' => 'width:100px;']) !!}</div>
                                <div class=" col-sm-1">{!! Form::label('value', 'Value:') !!}</div>
                                <div class=" col-sm-3">{!! Form::text('value', $intrAction->value, ['class' => 'form-control', 'style' => 'width:120px;']) !!}</div>
                                <div class=" col-sm-1">{!! Form::button('<span class="glyphicon glyphicon-minus-sign"></span>', array('class' => 'btn btn-primary remove-action')) !!}</div>
                            </div>
                        @endforeach
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
                                    {!! Form::button('Update Rule', array('class' => 'btn btn-success')) !!}
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
                <div class="form-group">
                    <div class=" col-sm-1">{!! Form::label('Condition', 'Condition:', array('class' => '')) !!}</div>
                    <div class=" col-sm-3">{!! Form::select('condition', $conditions, '', ['class' => 'form-control select-picker', 'style' => 'width:120px;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::label('Operator', 'Operator:', array('class' => '')) !!}</div>
                    <div class=" col-sm-2">{!! Form::select('operator', $operators, '', ['class' => 'form-control select-picker']) !!}</div>
                    <div class=" col-sm-1">{!! Form::label('Value', 'Value:', array('class' => '')) !!}</div>
                    <div class=" col-sm-3">{!! Form::text('value', '', ['class' => 'form-control', 'style' => 'width:100px;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::button('<span class="glyphicon glyphicon-minus-sign"></span>', array('class' => 'btn btn-primary remove-condition')) !!}</div>
                </div>
            </div>
            <div id="jsactions">
                <div class="form-group">
                    <div class=" col-sm-1">{!! Form::label('action', 'Action:') !!}</div>
                    <div class=" col-sm-3">{!! Form::select('action', $actions, '', ['class' => 'form-control select-picker', 'style' => 'width:120px;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::label('Operator', 'Operator:', array('class' => '')) !!}</div>
                    <div class=" col-sm-2">{!! Form::select('operator', $operators, '', ['class' => 'form-control select-picker', 'style' => 'width:100px;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::label('value', 'Value:') !!}</div>
                    <div class=" col-sm-3">{!! Form::text('value', '', ['class' => 'form-control', 'style' => 'width:120px;']) !!}</div>
                    <div class=" col-sm-1">{!! Form::button('<span class="glyphicon glyphicon-minus-sign"></span>', array('class' => 'btn btn-primary remove-action')) !!}</div>
                </div>
            </div>
        </div>


    </div>
@stop
