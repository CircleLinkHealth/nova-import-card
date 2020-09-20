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

    @include('core::partials.core::partials.errors.messages')

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
                                                <option value="{{$practice->id}}" @if(auth()->user()->isAdmin() && $practice->isARealBillableCustomer()) selected @endif>{{$practice->display_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

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
                                            <option value="pdf">PDF</option>
                                            <option value="csv" selected>QuickBooks CSV</option>
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
