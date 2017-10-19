@extends('partials.adminUI')

@section('content')

    @push('scripts')
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
    @endpush

    {!! Form::open(array('url' => URL::route('MonthlyBillingReportsController.makeMonthlyReport', array()), 'class' => 'form-horizontal')) !!}
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
                            <div class="col-sm-2">
                                {!! Form::label('ccm_time_minutes', 'CCM Time in Minutes:') !!}
                            </div>
                            <div class="col-sm-2">
                                {!! Form::text('ccm_time_minutes', '20', ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">
                                {!! Form::label('under', 'Under') !!}
                            </div>
                            <div class="col-sm-10">
                                <div class="col-sm-1">
                                    {!! Form::checkbox('under', 'true', false, ['class' => 'form-control']) !!}
                                </div>
                                check for under, leave unchecked for over
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2">
                                {!! Form::label('status', 'Status') !!}
                            </div>

                            <div class="col-sm-10">
                                <div class="col-sm-10">
                                    <div class="col-sm-1">
                                        {!! Form::checkbox('status[]', 'enrolled', true, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-sm-9">
                                        {!! Form::label('status', 'Enrolled') !!}
                                    </div>
                                </div>

                                <div class="col-sm-10">
                                    <div class="col-sm-1">
                                        {!! Form::checkbox('status[]', 'withdrawn', true, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-sm-9">
                                        {!! Form::label('status', 'Withdrawn') !!}
                                    </div>
                                </div>

                                <div class="col-sm-10">
                                    <div class="col-sm-1">
                                        {!! Form::checkbox('status[]', 'paused', true, ['class' => 'form-control']) !!}
                                    </div>
                                    <div class="col-sm-9">
                                        {!! Form::label('status', 'Paused') !!}
                                    </div>
                                </div>
                            </div>
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
                                    <div class="row" id="program_{{ $id }}">
                                        <div class="col-sm-12">
                                            {!! Form::checkbox('programs[]', $id, [], ['style' => '', 'class' => 'programs']) !!}
                                            {!! Form::label("label-$name", "$name - ID: $id", array('class' => '')) !!}
                                        </div>
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
