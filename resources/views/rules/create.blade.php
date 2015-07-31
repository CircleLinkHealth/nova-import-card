@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Add Rule</div>
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

                        {!! Form::open(array('url' => '/rules', 'class' => 'form-horizontal')) !!}
                        <strong>Conditions:</strong>
                        <div class="form-group">
                            <div class=" col-sm-2">{!! Form::label('Condition', 'Condition:', array('class' => '')) !!}</div>
                            <div class=" col-sm-10">{!! Form::select('condition', $conditions, '', ['class' => 'form-control select-picker']) !!}</div>
                            <div class=" col-sm-2">{!! Form::label('Operator', 'Operator:', array('class' => '')) !!}</div>
                            <div class=" col-sm-10">{!! Form::select('operator', $operators, '11', ['class' => 'form-control select-picker']) !!}</div>
                            <div class=" col-sm-2">{!! Form::label('Value', 'Value:', array('class' => '')) !!}</div>
                            <div class=" col-sm-10">{!! Form::text('value', '') !!}</div>
                        </div>

                        <strong>Actions:</strong>
                        <div class="form-group">
                            <div class=" col-sm-2">{!! Form::label('action', 'Action:') !!}</div>
                            <div class=" col-sm-10">{!! Form::select('action', $actions, '', ['class' => 'form-control select-picker']) !!}</div>
                            <div class=" col-sm-2">{!! Form::label('Operator', 'Operator:', array('class' => '')) !!}</div>
                            <div class=" col-sm-10">{!! Form::select('operator', $operators, '11', ['class' => 'form-control select-picker']) !!}</div>
                            <div class=" col-sm-2">{!! Form::label('value', 'Value:') !!}</div>
                            <div class=" col-sm-10">{!! Form::text('value', '') !!}</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::button('Add Rule', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
