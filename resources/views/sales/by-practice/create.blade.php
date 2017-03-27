@extends('partials.adminUI')

@section('content')

    <script>
        $(document).ready(function () {
            $(".practices").select2();

        });
    </script>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('reports.sales.practice.report', array()),'id' => 'form' ,'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="panel panel-default">
                    <div class="panel-heading">Practice Account Data Generator</div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            {{ csrf_field() }}
                            <fieldset>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="practice">
                                        Active Program<br>
                                        Select All <kbd><kbd>cmd</kbd> + <kbd>A</kbd></kbd></label>
                                    <div class="col-md-6">
                                        <select id="practice" name="practice" class="practices dropdown Valid form-control"
                                                required>
                                            @foreach($practices as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="sections">
                                        Sections<br>
                                        Select All <kbd><kbd>cmd</kbd> + <kbd>A</kbd></kbd></label>
                                    <div class="col-md-6">
                                        <select id="sections" name="sections[]" class=" dropdown Valid form-control"
                                                multiple required>
                                            @foreach($sections as $key => $value)
                                                <option value="{{$value}}">{{$key}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="start_date">From</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{\Carbon\Carbon::now()->startOfMonth()->toDateString()}}"
                                               name="start_date"
                                               id="start_date" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="end_date">To</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{\Carbon\Carbon::now()->toDateString()}}" name="end_date"
                                               id="end_date" required>
                                    </div>
                                </div>

                                <!-- Button -->
                                <div class="form-group">
                                    <div class="row" style="padding-left: 12px;">
                                        <label class="col-md-2 control-label" for="end_date"></label>
                                        <div class="col-md-6">
                                            <button id="submit" name="submit" value="display" class="btn btn-success">
                                                Generate Report
                                            </button>
                                            <button id="submit" name="submit" value="download" class="btn btn-success">
                                                Download Report
                                            </button>
                                        </div>

                                    </div>
                                </div>

                            </fieldset>
                        </form>

                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <div class="jumbotron text-center">
                                    <h1><span id="result"></span></h1>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
