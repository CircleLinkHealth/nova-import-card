@extends('partials.adminUI')

@section('content')

    <script>
        $(document).ready(function () {
            $(".nurses").select2();

        });
    </script>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('admin.reports.nurse.generate', array()),'class' => 'form-horizontal')) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">

                <div class="panel panel-default">
                    <div class="panel-heading">Practice Bill Generator</div>
                    <div class="panel-body">
                        <form class="form-horizontal">
                            {{ csrf_field() }}
                            <fieldset>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="days">
                                        Select Practices<br>
                                        Select All <kbd><kbd>cmd</kbd> + <kbd>A</kbd></kbd></label>
                                    <div class="col-md-6">
                                        <select id="nurse" name="nurses[]" class="nurses dropdown Valid form-control" multiple required>
                                            @foreach($practices as $practice)
                                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-2 control-label" for="end_date">Month</label>
                                    <div class="col-md-6">
                                        <input class="form-control" type="date"
                                               value="{{\Carbon\Carbon::now()->firstOfMonth()->toDateString()}}" name="start"
                                               id="end_date" disabled>
                                    </div>
                                </div>

                                <!-- Button -->
                                <div class="form-group">
                                    <div class="row" style="padding-left: 12px;">
                                        <label class="col-md-2 control-label" for="end_date"></label>
                                        <div class="col-md-2">
                                            <button id="submit" name="submit" value="download" class="btn btn-success">Download
                                                Invoice (s)
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


    <script>

        $('.collapse').collapse();

    </script>
@stop
