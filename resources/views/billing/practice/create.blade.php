@extends('partials.adminUI')

@section('content')

    <script>
        $(document).ready(function () {
            $(".practices").select2();

        });
    </script>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('practice.billing.make', array()),'class' => 'form-horizontal')) !!}
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
                                    <label class="col-md-2 control-label" for="end_date">Month</label>
                                    <div class="col-md-4">
                                        <input class="form-control" type="date"
                                               value="2017-03-01" name="start"
                                               id="end_date" disabled>
                                    </div>
                                    <div class="col-md-2">
                                    <button id="submit" name="submit" value="download"
                                            class="btn btn-success">
                                        Download Invoice (s)
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


    <script>

        $('.collapse').collapse();

    </script>
@stop
