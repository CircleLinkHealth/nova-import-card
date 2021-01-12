@extends('cpm-admin::partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => route('reports.sales.location.make', array()),'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="panel panel-default">
                    <div class="panel-heading">Account Status Report Generator</div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            {{ csrf_field() }}
                            <fieldset>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="days">
                                        Active Program<br>
                                        Select All <kbd><kbd>cmd</kbd> + <kbd>A</kbd></kbd></label>
                                    <div class="col-md-6">
                                        <select id="nurse" name="programs[]" class=" dropdown Valid form-control" multiple required>
                                            @foreach($programs as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="start_date">From</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{\Carbon\Carbon::now()->startOfMonth()->toDateString()}}" name="start_date"
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

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="end_date">Include Prior Month</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="checkbox"
                                               value="1" name="withPastMonth"
                                               id="withPastMonth">
                                    </div>
                                </div>


                                <!-- Button -->
                                <div class="form-group">
                                    <div class="row" style="padding-left: 12px;">
                                        <label class="col-md-2 control-label" for="end_date"></label>
                                        <div class="col-md-2">
                                            <button id="submit" name="submit" value="download" class="btn btn-success">
                                                Generate Report(s)
                                            </button>
                                        </div>
                                        <div class="col-md-2" style="padding-left: 40px">
                                            <button id="submit" name="submit" value="email" class="btn btn-success" disabled>Email (s)
                                            </button>
                                        </div>
                                    </div>
                                </div>


                            </fieldset>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            $('.collapse').collapse();
        </script>
    @endpush
@stop
