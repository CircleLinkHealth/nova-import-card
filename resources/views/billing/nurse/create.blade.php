@extends('partials.adminUI')

@section('content')

    @push('scripts')
        <script>
            $(document).ready(function () {
                $(".nurses").select2();

            });
        </script>
    @endpush

    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => route('admin.reports.nurse.generate', array()),'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="panel panel-default">
                    <div class="panel-heading">Nurse Invoice Generator</div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            {{ csrf_field() }}
                            <fieldset>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="days">
                                        Active Nurse<br>
                                        Select All <kbd><kbd>cmd</kbd> + <kbd>A</kbd></kbd></label>
                                    <div class="col-md-6">
                                        <select id="nurse" name="nurses[]" class="nurses dropdown Valid form-control" multiple required>
                                            @foreach($nurses as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="start_date">From</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{presentDate(\Carbon\Carbon::now()->startOfMonth(), false)}}" name="start_date"
                                               id="start_date" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="end_date">To</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{presentDate(\Carbon\Carbon::now()->endOfMonth(), false)}}" name="end_date"
                                               id="end_date" required>
                                    </div>
                                </div>

                                <div class="form-group" style="padding-left:40px;">
                                    <label data-target="#collapseOne" class="col-md-3"
                                           style="width: 25%">
                                        <div class="radio"><input type="checkbox" name="alternative_pay"
                                                                  id="alternative_pay"
                                                                  value="alternative_pay"/><label
                                                    for="alternative_pay"><span> </span>Alternative Pay</label>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-group" style="padding-left:40px;">
                                    <div class="panel-group" id="accordion">

                                        <div>
                                            <label data-toggle="collapse" data-target="#collapseOne" class="col-md-1"
                                                   style="width: 13%">
                                                <div class="radio"><input type="checkbox" name="has_extra_time"
                                                                          id="has_extra_time"
                                                                          value="has_extra_time"/><label
                                                            for="phone"><span> </span>Add Time</label>
                                                </div>
                                            </label>
                                        </div>

                                        <div id="collapseOne" class="panel-collapse collapse in">

                                            <div class="form-group">
                                                <div class="col-md-6">
                                                    <input class="form-control" type="number"
                                                           value="" placeholder="Enter Time in Minutes"
                                                           name="manual_time"
                                                           id="manual_time">
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <div class="col-md-6">
                                                    <input class="form-control" type="text"
                                                           value="" placeholder="Enter Notes"
                                                           name="manual_time_notes" style="margin-left: 30%"
                                                           id="manual_time_notes">
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <!-- Button -->
                                <div class="form-group">
                                    <div class="row" style="padding-left: 12px;">
                                        <label class="col-md-2 control-label" for="end_date"></label>
                                        <div class="col-md-2">
                                            <button id="submit" name="submit" value="download" class="btn btn-default">Download
                                                Invoice(s) V1 (to be removed)
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row" style="padding-left: 12px;">
                                        <label class="col-md-2 control-label" for="end_date"></label>
                                        <div class="col-md-2">
                                            <button id="submit" name="submit" value="downloadV2" class="btn btn-success">Download
                                                Invoice(s) V2 (beta)
                                            </button>
                                        </div>
                                    </div>
                                </div>


                            </fieldset>


                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}

    @push('scripts')
        <script>
            $('.collapse').collapse();
        </script>
    @endpush
@stop
