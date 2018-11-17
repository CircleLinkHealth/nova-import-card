@extends('partials.adminUI')

@section('content')
    <div class="container">
        <div class="row">
            @if(Session::has('message'))
                <div class="col-md-12">
                    @if(Session::get('type') == 'success')
                        <div class="alert alert-success">
                            {!!Session::get('message')!!}
                        </div>
                    @elseif(Session::get('type') == 'error')
                        <div class="alert alert-danger">
                            {!!Session::get('message')!!}
                        </div>
                    @else
                        <div class="alert alert-info">
                            {!!Session::get('message')!!}
                        </div>
                    @endif
                </div>
            @endif

            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <b>{{ $batch->practice->display_name }}</b> | Batch #{{$batch->id}}
                        | Started: <em>{{ $batch->created_at->format('m-d-Y h:mA') }}</em> | Last Update:
                        <em>{{ $batch->updated_at->format('m-d-Y h:mA')}}</em>
                        <div class="pull-right" style="color: {{$batch->getStatusFontColor()}};">
                            @if ($batch->status != 0)
                                <b>{{ strtoupper($batch->getStatus()) }}</b></div>
                        @endif
                    </div>

                    <div class="panel-body">
                        @if($batch->hasJobs())
                            <div class="pull-left" style="padding-left: 2%;">
                                <a href="{{route('eligibility.download.logs.csv', [$batch->id])}}"
                                   class="btn btn-warning">Download Batch Processing Logs</a>
                            </div>
                        @endif

                        @if($enrolleesExist)
                            <div class="pull-left" style="padding-left: 2%;">
                                <a href="{{route('admin.enrollees.show.batch', [$batch->id])}}"
                                   class="btn btn-info">Import Patients</a>
                            </div>

                            @if(\Cache::has("batch:{$batch->id}:last_consented_enrollee_import"))
                                <div class="pull-left" style="padding-left: 2%;">
                                    <a href="{{route('eligibility.download.last.import.logs', [$batch->id])}}"
                                       class="btn btn-warning">Download Last Import Session Logs</a>
                                </div>
                            @endif

                            <div class="pull-left" style="padding-left: 2%;">
                                <a href="{{route('eligibility.download.eligible', [$batch->id])}}"
                                   class="btn btn-default">Download
                                    Eligible Patients CSV</a>
                            </div>
                        @endif

                        @if ($batch->type == App\EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE)
                            <div class="pull-left" style="padding-left: 2%;">
                                <a href="{{route('get.eligibility.reprocess', [$batch->id])}}"
                                   class="btn btn-danger">Reprocess</a>
                            </div>

                            <div class="pull-left" style="padding-left: 2%;">
                                <a href="{{route('eligibility.download.csv.patient.list', [$batch->id])}}"
                                   class="btn btn-info">All patients CSV</a>
                            </div>
                        @endif
                        @if($fromReportWriter)
                            <div class="pull-right" style="padding-left: 2%;">
                                <a href=""
                                   class="btn btn-info">Notify Report Writer</a>
                            </div>
                        @endif


                        <br><br>

                        <div class="col-md-6">
                            <h4>Counts</h4>

                            @if(isset($stats) && !empty($stats))
                                @forelse($stats as $key => $value)
                                    <b>{{snakeToSentenceCase(snake_case($key))}}</b>: {{$value}}<br>
                                @empty
                                    <p>No stats found</p>
                                @endforelse
                            @else
                                Eligible: <span id="eligible">{{ $eligible }}</span>
                                <br>
                                Ineligible: <span id="ineligible">{{ $ineligible }}</span>
                                <br>
                                Duplicates: <span id="duplicates">{{ $duplicates }}</span>
                                <br>
                                Not processed: <span id="unprocessed">{{ $unprocessed }}</span>
                            @endif

                            <br><br>

                            <h4>Processing Options</h4>

                            @forelse($batch->options as $k => $option)
                                @if(!is_array($option))
                                    <b>{{snakeToSentenceCase(snake_case($k))}}</b>
                                    : @if(is_bool($option)) {{!!$option ? 'Yes' : 'No'}} @else {{$option}} @endif<br>
                                @endif
                            @empty
                                <p>No options found.</p>
                            @endforelse
                        </div>
                        @if($fromReportWriter)
                            <h4>Validation Stats</h4>
                            Total records: {{$validationStats['total']}}<br>

                            Total records with invalid data structure: {{$validationStats['invalid_structure']}}<br>

                            Total records with invalid data: {{$validationStats['invalid_data']}}<br>
                            Missing/invalid mrn: {{$validationStats['mrn']}}<br>
                            Missing/invalid name: {{$validationStats['name']}}<br>
                            Invalid DOB: {{$validationStats['dob']}}<br>
                            0 problems: {{$validationStats['problems']}}<br>
                            0 phones: {{$validationStats['phones']}}<br>

                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection