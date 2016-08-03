@extends('partials.adminUI')

@section('content')

    <script>
        $(document).ready(function () {
            $(function () {
                $("#programsCheckAll").click(function () {
                    $(".programs").prop("checked", true);
                    return false;
                });

                $("#programsUncheckAll").click(function () {
                    $(".programs").prop("checked", false);
                    return false;
                });
            });
        });
    </script>

    {!! Form::open(array('url' => URL::route('admin.appConfig.store', array()), 'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Monthly Billing Reports</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                @include('errors.errors')
                @include('errors.messages')
                <div class="panel panel-default">
                    <div class="panel-heading">Create</div>
                    <div class="panel-body">

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('ccm_time_minutes', 'CCM Time in Minutes:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('ccm_time_minutes', '20', ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('month', 'Month:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('month', date('m'), ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('year', 'Year:') !!}</div>
                            <div class="col-sm-10">{!! Form::text('year', date('Y'), ['class' => 'form-control', 'style' => 'width:50%;']) !!}</div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">{!! Form::label('programs', 'Programs:') !!}</div>
                            <div class="col-sm-10">
                                <button class="btn-primary btn-xs" id="programsCheckAll">Check All</button>
                                |
                                <button class="btn-primary btn-xs" id="programsUncheckAll">Uncheck All</button>
                                @foreach( $programs as $id => $name )
                                    <div class="row" id="program_{{ $id }}"
                                         style="border-bottom:1px solid #000;">
                                        <div class="col-sm-2">
                                            <div class="text-right">
                                                {!! Form::checkbox('programs[]', $id, [], ['style' => '', 'class' => 'programs']) !!}
                                            </div>
                                        </div>
                                        <div class="col-sm-10">{!! Form::label("label-$name", "$name - ID: $id", array('class' => '')) !!}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::submit('Create Report', array('class' => 'btn btn-success')) !!}
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
