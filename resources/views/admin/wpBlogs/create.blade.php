@extends('partials.adminUI')

@section('content')
    <style>
        .form-group {
            margin:20px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>Add New Practice</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Add New Practice
                    </div>
                    <div class="panel-body">

                        <div class="row">
                            {!! Form::open(array('url' => URL::route('admin.programs.store', array()), 'class' => 'form-horizontal')) !!}
                        </div>

                        <div class="row" style="">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Add Practice', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>

                        <h2>New Practice</h2>

                        <div class="form-group">

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('display_name', 'Display Name:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('display_name', '', ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('name', 'Unique Name:') !!}</div>
                                <div class="col-xs-10">{!! Form::text('name', '', ['class' => 'form-control', 'style' => 'width:100%;']) !!}</div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('weekly_report_recipients', 'Weekly Organization Summary Recipients (comma separated, w/ spaces after comma) ') !!}</div>
                                <div class="col-xs-10"><textarea name="weekly_report_recipients" style="width: 100%"> </textarea></div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('clh_pppm', 'CPM Price') !!}</div>
                                <div class="col-xs-10"><input class="form-control" name="clh_pppm" style="width: 100%"/>
                                </div>
                            </div>

                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('term_days', 'Terms') !!}</div>
                                <div class="col-xs-10"><input class="form-control" name="term_days" style="width: 100%" value="30"/>
                                </div>
                            </div>


                            <div class="row" style="margin-top:20px;">
                                <div class="col-xs-2">{!! Form::label('active', 'Active') !!}</div>
                                <div class="col-xs-10"><input type="checkbox" class="form-control" name="active" style="width: 100%" checked/>
                                </div>
                            </div>


                        </div>



                        <div class="row" style="margin-top:50px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    <a href="{{ URL::route('admin.programs.index', array()) }}" class="btn btn-danger">Cancel</a>
                                    {!! Form::submit('Add Practice', array('class' => 'btn btn-success')) !!}
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