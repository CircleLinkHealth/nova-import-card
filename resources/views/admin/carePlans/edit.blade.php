@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('admin.careplans.update', array('id' => $carePlan->id)), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-2">
                        <h1>Items</h1>
                    </div>
                    <div class="col-sm-10">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ URL::route('admin.careplans.create', array()) }}" class="btn btn-success">Duplicate Care Plan</a>
                            <a href="{{ URL::route('admin.careplans.create', array()) }}" class="btn btn-success">Duplicate for patient</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Care Plan: {{ $carePlan->id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('user_id', 'User:') !!}</div>
                            <div class="col-sm-4">{!! Form::select('user_id', array('' => 'No User') + $users, $carePlan->user_id, ['class' => 'form-control select-picker', 'style' => 'width:80%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('name', 'Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('name', $carePlan->name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('display_name', $carePlan->display_name, ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('type', 'Type:') !!}</div>
                            <div class="col-sm-10">{!! Form::select('type', array('test' => 'test', 'provider' => 'provider', 'provider-default' => 'provider-default','patient' => 'patient', 'patient-default' => 'patient-default'), $carePlan->type, ['class' => 'form-control select-picker', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <h2>Sections:</h2>
                        <a href="{{ URL::route('admin.careplansections.create', array()) }}" class="btn btn-primary btn">
                            <span class="glyphicon glyphicon-plus-sign"></span>
                            Add Section
                        </a>
                        <br />



                        @if($carePlan->careSections)
                            @foreach($carePlan->careSections as $careSection)
                                <div style="border:5px solid green;margin-top:20px;">
                                <h2>section {{ $careSection->id }} {{ $careSection->display_name }}</h2>

                                @if(!empty($careSection->planItems))
                                    <?php $i=0; ?>
                                    @foreach($careSection->planItems as $planItem)
                                        @if($i % 2 == 0) START ROW<div class="row"> @endif
                                        <div class="col-sm-6">
                                            {{ $planItem->ui_row_start > 0 ? '<div class="row">' : '' }}
                                            {{ $planItem->ui_col_start > 0 ? '<div class="col-sm-'.$planItem->ui_col_start.'>' : '' }}
                                                <div style="border:1px solid blue;margin:0px;padding:0px;">
                                                    <strong>{{ $planItem->careItem->display_name }}</strong><br />
                                                    <strong>{{ $planItem->meta_key . ' = ' . $planItem->meta_value }}</strong><br />
                                                    [EYE:{{ $i+1 .' of '.$careSection->planItems->count() }}]<br />
                                                    [CarePlanItem:{{ $planItem->id }}]<br />
                                                    [ui_fld_type:{{ $planItem->ui_fld_type }}]<br />
                                                    [ui_row_start:{{ $planItem->ui_row_start }}]<br />
                                                    [ui_row_end:{{ $planItem->ui_row_end }}]<br />
                                                    [ui_col_start:{{ $planItem->ui_col_start }}]<br />
                                                    [ui_default:{{ $planItem->ui_default }}]<br />Other
                                                    [obs_key:{{ $planItem->careItem->obs_key }}]<br />
                                                    @if (!is_null($planItem->children))
                                                        @foreach($planItem->children as $planItemChild)
                                                            {!! $planItemChild->ui_row_start > 0 ? '<div class="row">' : '' !!}
                                                                @if ($planItemChild->ui_col_start > 0)
                                                                    <div class="col-sm-{!! $planItemChild->ui_col_start !!}">
                                                                @endif
                                                                <strong>{{ $planItemChild->careItem->display_name }}</strong><br />
                                                                <strong>{{ $planItemChild->meta_key . ' = ' . $planItemChild->meta_value }}</strong><br />
                                                                [ui_fld_type:{{ $planItemChild->ui_fld_type }}]<br />
                                                                [ui_row_start:{{ $planItemChild->ui_row_start }}]<br />
                                                                [ui_row_end:{{ $planItemChild->ui_row_end }}]<br />
                                                                [ui_col_start:{{ $planItemChild->ui_col_start }}]<br />
                                                                [ui_default:{{ $planItemChild->ui_default }}]<br />
                                                                [ui_sort:{{ $planItemChild->ui_sort }}]<br />
                                                                @if ($planItemChild->ui_col_end > 0)
                                                                    </div>
                                                                @endif
                                                            {!! $planItemChild->ui_row_end > 0 ? '</div>' : '' !!}
                                                        @endforeach
                                                    @endif
                                                </div>
                                            {{ $planItem->ui_row_end > 0 ? '</div>' : '' }}
                                            {{ $planItem->ui_col_end > 0 ? '</div>' : '' }}
                                        @if( ($i % 2 != 0) || ($careSection->planItems->count() == ($i+1)) ) END ROW</div> @endif
                                        <?php $i++; ?>
                                    </div>

                                    @endforeach
                                @endif

                                </div>
                            @endforeach
                        @endif


                        @if($carePlan->careSections)
                            <a href="{{ URL::route('admin.careplans.index', array()) }}" class="btn btn-primary btn">
                                <span class="glyphicon glyphicon-plus-sign"></span>
                                Add Item
                            </a>
                            <h3>Section 1:</h3>
                            <a href="{{ URL::route('admin.items.show', array('id' => $carePlan->id)) }}" class="btn btn-orange btn-xs">{{ $carePlan->name }}</a>
                        @else
                            <div class="alert alert-danger" style="margin-top:20px;">
                                No sections
                            </div>
                        @endif

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.careplans.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Edit Care Plan', array('class' => 'btn btn-success')) !!}
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
