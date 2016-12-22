@extends('partials.adminUI')

@section('content')
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    {!! Form::open(array('url' => URL::route('reports.sales.location.make', array()),'class' => 'form-horizontal')) !!}
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
                                    <label class="col-md-2 control-label" for="practices">
                                        Active Program<br>
                                        Select All <kbd><kbd>cmd</kbd> + <kbd>A</kbd></kbd></label>
                                    <div class="col-md-6">
                                        <select id="practices" name="practices[]" class=" dropdown Valid form-control" multiple required>
                                            @foreach($practices as $key => $value)
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

                                <input class="form-control" type="hidden"
                                       value="{{\Carbon\Carbon::now()->toDateString()}}" name="end_date"
                                       id="end_date" required>

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
