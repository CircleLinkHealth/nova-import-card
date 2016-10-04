@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('admin.reports.nurse.generate', array()),'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">

                <div class="panel panel-default">
                    <div class="panel-heading">Nurse Invoice Generator</div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            <fieldset>

                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="days">Active Nurse</label>
                                    <div class="col-md-4">
                                        <select id="nurse" name="nurse" class=" dropdown Valid form-control">
                                            @foreach($nurses as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="start_date">From</label>
                                    <div class="col-md-4">
                                        <input class="form-control" type="datetime" value="{{\Carbon\Carbon::now()->startOfMonth()}}" name="start_date" id="start_date">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="end_date">To</label>
                                    <div class="col-md-4">
                                        <input class="form-control" type="datetime" value="{{\Carbon\Carbon::now()->endOfMonth()}}" name="end_date" id="end_date">
                                    </div>
                                </div>

                                <!-- Button -->
                                <div class="form-group">
                                    <label class="col-md-4 control-label" for="submit"></label>
                                    <div class="col-md-4">
                                        <button id="submit" name="submit" class="btn btn-success">Download Invoice</button>
                                    </div>
                                </div>

                            </fieldset>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
