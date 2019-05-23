@extends('partials.adminUI')

@section('content')

    @push('scripts')
        <script>
            $(document).ready(function () {
                $(".nurses").select2({
                    placeholder: 'Select specific Nurses, or click checkbox to the right for all nurses',
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
                    <div class="panel-heading">Nurse Invoice Generator</div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            {{ csrf_field() }}
                            <fieldset>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="days">
                                        Nurses<br></label>
                                    <div class="col-md-6">
                                        <select id="nurses-select" name="nurses[]"
                                                class="nurses dropdown Valid form-control"
                                                multiple required>
                                            @foreach($nurses as $key => $value)
                                                <option value="{{$key}}">{{$value}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label data-target="#collapseOne" class="col-md-10 col-md-offset-2">
                                        <div class="radio"><input type="checkbox" name="all_selected_nurses"
                                                                  id="selectAll"
                                                                  onclick="disableTextInput()"
                                                                  value="all_selected_nurses"/>
                                            <label for="selectAll"><span> </span>Select all Nurses who logged in during below range</label>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="start_date">From</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{presentDate(\Carbon\Carbon::now()->startOfMonth(), false)}}"
                                               name="start_date"
                                               id="start_date" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="end_date">To</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{presentDate(\Carbon\Carbon::now()->endOfMonth(), false)}}"
                                               name="end_date"
                                               id="end_date" required>
                                    </div>
                                </div>

                                <!-- Button -->
                                <div class="form-group">
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
