@extends('partials.adminUI')

@section('content')
    @push('scripts')
        <script type="text/javascript" src="{{ asset('/js/admin/reports/nurseTimeReport.js') }}"></script>
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Nurse Time Report</h1>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-body">
                        {!! Form::open(array('url' => URL::route('admin.reports.nurseTime.index', array()), 'method' => 'get', 'class' => 'form-horizontal', 'id' => 'filters')) !!}
                        <div class="row">
                            <div class="col-xs-2"><label for="start_date">Start Date:</label></div>
                            <div class="col-xs-4"><input id="start_date" class="form-control" name="start_date" type="input" value="{{ (old('start_date') ? old('start_date') : ($startDate->format('Y-m-d') ? $startDate->format('Y-m-d') : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><span class="help-block">{{ $errors->first('start_date') }}</span></div>
                            <div class="col-xs-2"><label for="end_date">End Date:</label><div id="dtBox"></div></div>
                            <div class="col-xs-4"><input id="end_date" class="form-control" name="end_date" type="input" value="{{ (old('end_date') ? old('end_date') : ($endDate->format('Y-m-d') ? $endDate->format('Y-m-d') : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><span class="help-block">{{ $errors->first('end_date') }}</span></div>
                        </div>
                        <div class="row pull-right" style="margin:0px 20px;">
                            <input type="checkbox" name="showAllTimes" value="checked" {{ (($showAllTimes == 'checked')) ? 'checked="checked"' : '' }}> show total time (default is ccm time only)<br>
                        </div>
                        <div class="row" style="margin-top:60px;">
                            <div class="col-sm-12">
                                <div class="pull-right">
                                    {!! Form::submit('Apply', array('class' => 'btn btn-success')) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading"></div>
                    <div class="panel-body">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                @foreach( $reportColumns as $column )
                                    <td>{{ $column }}</td>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach( $reportRows as $reportRow )
                                <tr>
                                    @foreach( $reportRow as $column )
                                        <td>{{ $column }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <a href="{{ URL::route('admin.reports.nurseTime.exportxls', array(
                'start_date' => (old('start_date') ? old('start_date') : ($startDate->format('Y-m-d') ? $startDate->format('Y-m-d') : '')),
                'end_date' => (old('end_date') ? old('end_date') : ($endDate->format('Y-m-d') ? $endDate->format('Y-m-d') : ''))
                )) }}" class="btn btn-success pull-right">Excel Export</a>
            </div>
        </div>
    </div>
@stop
