@extends('partials.adminUI')

@section('content')

    @push('scripts')
        <script>
            $(document).ready(function () {
                $(".nurses").select2({
                    placeholder: 'Select specific nurses, or checkbox below',
                    allowClear: true
                });
            });
        </script>
    @endpush

    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    {!! Form::open(array('url' => route('admin.reports.nurse.generate'),'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Nurse Invoice Generator <span class="pull-right"> <a href="javascript:void(0);" onclick="javascript:introJs().setOption('showProgress', true).start();"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></a></span></div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            {{ csrf_field() }}
                            <fieldset>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="days">
                                        Nurses</label>
                                    <div class="col-md-6" data-step="1" data-intro="If you want to generate invoices for specific nurses, use this search box to select them.">
                                        <select id="nurses-select" name="nurses[]"
                                                class="nurses dropdown Valid form-control"
                                                multiple required>
                                            @foreach($nurses as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group" data-step="2" data-intro="Alternatively, you can check this box and the system will generate invoices for all nurses who have any system time in the selected date range. Checking this checkbox will clear the search box above.">
                                    <label data-target="#collapseOne" class="col-md-10 col-md-offset-2">
                                        <div class="radio"><input type="checkbox" name="all_selected_nurses"
                                                                  id="selectAll"
                                                                  onclick="disableTextInput()"
                                                                  value="all_selected_nurses"/>
                                            <label for="selectAll"><span> </span>Select all nurses who logged in during below range</label>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-group" data-step="3" data-intro="Invoices will be generated from the beginning of this day (12:00:00am).">
                                    <label class="col-md-2 control-label" for="start_date">From</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{presentDate(\Carbon\Carbon::now()->startOfMonth(), false)}}"
                                               name="start_date"
                                               id="start_date" required>
                                    </div>
                                </div>

                                <div class="form-group" data-step="4" data-intro="Until the end of this day (11:59:59pm).">
                                    <label class="col-md-2 control-label" for="end_date">To</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{presentDate(\Carbon\Carbon::now()->endOfMonth(), false)}}"
                                               name="end_date"
                                               id="end_date" required>
                                    </div>
                                </div>

                                <!-- Button -->
                                <div class="form-group" data-step="5" data-intro="Once you've finalized your options, click this button. The system will generate invoices in the background and send you an email when done.">
                                    <div class="row" style="padding-left: 12px;">
                                        <label class="col-md-2 control-label" for="end_date"></label>
                                        <div class="col-md-2">
                                            <button id="submit" name="submit" class="btn btn-success">Generate
                                                Invoice(s)
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
    {!! Form::close() !!}

    @push('scripts')
        <script>
            function disableTextInput() {
                var checkBox = document.getElementById("selectAll");
                document.getElementById("nurses-select").disabled = checkBox.checked === true;

                $(".nurses").val(null).trigger('change')
            }
        </script>
    @endpush
@stop
