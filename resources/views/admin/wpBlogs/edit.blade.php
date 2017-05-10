@extends('partials.adminUI')

@section('content')
    <style>
        .form-group {
            margin: 20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Edit Practice: {{ $program->display_name }}</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Practice ID: {{ $program->id }}
                    </div>
                    <div class="panel-body">
                        @include('errors.errors')

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.programs.update', array('id' => $program->id)), 'class' => 'form-horizontal')) !!}
                        </div>

                        <div class="row" style="">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Practice', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('display_name', $program->display_name, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('name', 'Unique Name:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('name', $program->name, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('bill_to_name', 'Bill To:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('bill_to_name', $program->bill_to_name, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('invoice_recipients', 'Invoice Recipients (comma separated, w/ spaces after comma)') !!}</div>
                                <div class="col-xs-10"><textarea class="form-control" name="invoice_recipients"
                                                                 style="width: 100%">@if(isset($program->invoice_recipients)){{$program->invoice_recipients}}@endif</textarea>
                                    <small>The emails above will receive invoices @if($recipients), in addition to {{$recipients}}. @endif</small>
                                </div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('weekly_report_recipients', 'Weekly Organization Summary Recipients (comma separated, w/ spaces after comma)') !!}</div>
                                <div class="col-xs-10"><textarea class="form-control" name="weekly_report_recipients"
                                                                 style="width: 100%">@if(isset($program->weekly_report_recipients)){{$program->weekly_report_recipients}}@endif</textarea>
                                    <small>The emails above will receive weekly summary reports.</small>
                                </div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('clh_pppm', 'CPM Price') !!}</div>
                                <div class="col-xs-3"><input class="form-control" name="clh_pppm" style="width: 100%"
                                                             @if(isset($program->clh_pppm)) value="{{$program->clh_pppm}}" @endif/>
                                </div>
                                <div class="col-xs-1">{!! Form::label('term_days', 'Terms (days)') !!}</div>
                                <div class="col-xs-3"><input class="form-control" name="term_days" style="width: 100%"
                                                             @if(isset($program->term_days)) value="{{$program->term_days}}" @endif/>
                                </div>
                            </div>

                            <div class="row" style="margin-top:20px;">

                                <div class="col-xs-2">{!! Form::label('active', 'Active Practice') !!}</div>
                                <div class="col-xs-10"><input class="form-control" type="checkbox" name="active"
                                                              style="width: 100%"
                                                              @if($program->active == 1) checked @endif/>
                                </div>

                            </div>
                            <div class="row" style="margin-top:20px;">

                                <label class="col-md-2 control-label" for="primary_location">
                                    Select Primary Location<br></label>
                                <div class="col-md-8">
                                    <select id="primary_location" name="primary_location"
                                            class="primary_location dropdown Valid form-control" required>
                                        @foreach($locations as $location)
                                            <option value="{{$location->id}}" @if($location->is_primary) selected @endif>{{$location->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                        </div>

                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Update Practice', array('class' => 'btn btn-success')) !!}
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop