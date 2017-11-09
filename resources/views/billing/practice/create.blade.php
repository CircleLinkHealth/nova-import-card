@extends('partials.adminUI')

@section('content')

    @push('scripts')
        <script>
            $(document).ready(function () {
                let practices = $(".practices")

                practices.select2()

                practices.on('select2:select', function(e){
                    var id = e.params.data.id;
                    var option = $(e.target).children('[value='+id+']');
                    option.detach();
                    $(e.target).append(option).change();
                });
            });
        </script>
    @endpush

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('practice.billing.make', array()),'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Practice Invoice and Patient Report Generator</div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            {{ csrf_field() }}
                            <fieldset>
                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="practices">
                                        Select Practices<br>
                                        Select All <kbd><kbd>cmd</kbd> + <kbd>A</kbd></kbd></label>
                                    <div class="col-md-6">
                                        <select id="practices" name="practices[]"
                                                class="practices dropdown Valid form-control" multiple required>
                                            @foreach($readyToBill as $practice)
                                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">

                                    <label class="col-md-2 control-label" for="invoice_no">
                                        Custom Invoice#<br></label>
                                    <div class="col-md-6">
                                        <input class="form-control" value="{{$invoice_no}}" name="invoice_no" id="invoice_no">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="date">Month</label>
                                    <div class="col-md-4">
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
                                    <div class="col-md-2">
                                        <button id="submit" name="submit" value="download"
                                                class="btn btn-success">
                                            Create Invoice(s) / Report(s)
                                        </button>
                                    </div>
                                </div>
                            </fieldset>
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
