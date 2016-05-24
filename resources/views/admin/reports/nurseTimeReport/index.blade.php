@extends('partials.adminUI')

@section('content')
    <script type="text/javascript" src="{{ asset('/js/admin/reports/nurseTimeReport.js') }}"></script>
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
                {!! Form::open(array('url' => URL::route('admin.reports.nurseTime.index', array()), 'method' => 'get', 'class' => 'form-horizontal', 'id' => 'filters')) !!}
                <div class="row">
                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('start_date') ? 'has-error' : '' }}">
                        <label for="start_date">Start Time <span class="attention">*</span>:</label>
                        <input id="start_date" class="form-control" name="start_date" type="input" value="{{ (old('start_date') ? old('start_date') : ($startDate->format('Y-m-d') ? $startDate->format('Y-m-d') : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><br />
                        <span class="help-block">{{ $errors->first('start_date') }}</span>
                        <div id="dtBox"></div>
                    </div>

                    <div class="form-group form-item form-item-spacing col-sm-12 {{ $errors->first('end_date') ? 'has-error' : '' }}">
                        <label for="end_date">End Time <span class="attention">*</span>:</label>
                        <input id="end_date" class="form-control" name="end_date" type="input" value="{{ (old('end_date') ? old('end_date') : ($endDate->format('Y-m-d') ? $endDate->format('Y-m-d') : '')) }}"  data-field="date" data-format="yyyy-MM-dd" /><br />
                        <span class="help-block">{{ $errors->first('end_date') }}</span>
                    </div>
                </div>
                <div class="row" style="margin-top:50px;">
                    <div class="col-sm-12">
                        <div class="pull-right">
                            {!! Form::submit('Apply', array('class' => 'btn btn-success')) !!}
                        </div>
                    </div>
                </div>
                </form>
                <div class="panel panel-default">
                    <div class="panel-heading"></div>
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
        </div>
    </div>
@stop
