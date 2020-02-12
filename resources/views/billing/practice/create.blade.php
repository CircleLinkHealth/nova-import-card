@extends('partials.providerUI')

@section('title', 'Practice Billing')
@section('activity', 'Practice Billing')

@section('content')

    @push('scripts')
        <script>
            $(document).ready(function () {
                let practices = $(".practices")

                practices.select2()

                //show selections in the order they were selected
                practices.on('select2:select', function (e) {
                    var id = e.params.data.id;
                    var option = $(e.target).children('[value=' + id + ']');
                    option.detach();
                    $(e.target).append(option).change();
                });
            });
        </script>
    @endpush

    @include('errors.messages')

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => route('practice.billing.make', array()),'class' => 'form-horizontal')) !!}
    <div class="container-fluid" style="padding-top: 50px;">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Billable Patient Report Generator (Only shows approved patients > 20 minutes)</div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            {{ csrf_field() }}
                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="practices">
                                        Select Practices
                                    </label>
                                    <div class="col-md-6">
                                        <select id="practices" name="practices[]"
                                                class="practices dropdown Valid form-control" multiple required>
                                            @foreach($readyToBill as $practice)
                                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{--<div class="form-group">--}}
                                    {{--<label class="col-md-2 control-label" for="invoice_no">--}}
                                        {{--Custom Invoice#<br></label>--}}
                                    {{--<div class="col-md-6">--}}
                                        {{--<input class="form-control" value="{{$invoice_no}}" name="invoice_no"--}}
                                               {{--id="invoice_no">--}}
                                    {{--</div>--}}
                                {{--</div>--}}

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="date">Month</label>
                                    <div class="col-md-6">
                                        <select class="col-md-3 practices dropdown Valid form-control reloader"
                                                name="date" id="date">
                                            @foreach($dates as $key => $val)

                                                @if(\Carbon\Carbon::today()->firstOfMonth()->toDateString() == $key)
                                                    <option value="{{$key}}" selected>{{$val}}</option>
                                                @else
                                                    <option value="{{$key}}">{{$val}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="format">Select Format</label>
                                    <div class="col-md-6">
                                        <select class="col-md-3 practices dropdown Valid form-control reloader"
                                                name="format" required>
                                            <option value="pdf" selected>PDF</option>
                                            <option value="csv">QuickBooks CSV</option>
                                            <option value="xls">QuickBooks Excel</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="col-md-2 control-label">
                                        <button id="submit" name="submit" value="download"
                                                class="btn btn-success">
                                            Create Invoice(s) / Report(s)
                                        </button>
                                    </div>
                                </div>
                        </form>

                        <hr>

                        <div class="row" style="padding-left: 30px;">

                            List of Practices with pending QA:
                            <ul>

                                @foreach($needsQA as $practice)

                                    <li><a href="#">{{$practice->display_name}}</a></li>

                                @endforeach

                            </ul>

                        </div>
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
