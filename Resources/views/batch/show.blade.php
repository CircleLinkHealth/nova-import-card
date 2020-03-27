@extends('partials.adminUI')

@section('content')
    <div class="container">
        <div class="row">
            @if (!empty($batch->options['errors']) && is_array($batch->options['errors']))
                <div class="col-md-12">
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($batch->options['errors'] as $err)
                                <li>{{$err}}</li>
                            @endforeach
                        </ul>
                    </div>
            @endif

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
                        <b>{{ $batch->practice->display_name }}</b> | Batch #{{$batch->id}} | Type: {{$batch->getType()}}
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
                                <a href="{{route('eligibility.download.csv.eligible', [$batch->id])}}"
                                   class="btn btn-success">Download Eligible Patients CSV</a>
                            </div>
                        @endif

                        @if ($batch->type == CircleLinkHealth\Eligibility\Entities\EligibilityBatch::CLH_MEDICAL_RECORD_TEMPLATE)
                            <div class="pull-left" style="padding-left: 2%;">
                                <a href="{{route('get.eligibility.reprocess', [$batch->id])}}"
                                   class="btn btn-danger">Reprocess</a>
                            </div>

                            <div class="pull-left" style="padding-left: 2%;">
                                <a href="{{route('eligibility.download.csv.patient.list', [$batch->id])}}"
                                   class="btn btn-info">All patients CSV</a>
                            </div>
                        @else
                                <div class="pull-left" style="padding-left: 2%;">
                                    <a href="{{route('eligibility.download.all', [$batch->id])}}"
                                       class="btn btn-default">Download All Patients CSV</a>
                                </div>
                            @endif

                        @if(optional($initiatorUser)->hasRole('ehr-report-writer'))
                            <div class="pull-right" style="padding-left: 2%;">
                                <button class="btn btn-primary" onclick="notifyReportWriter()">Notify Report Writer
                                </button>
                            </div>
                        @endif


                        <br><br>

                            <div class="row">
                                @if (CircleLinkHealth\Eligibility\Entities\EligibilityBatch::ATHENA_API == $batch->type)
                                    <div class="col-md-12">ELIGIBLES ARE PATIENTS W/ OFFICE VISIT IN LAST 12 MONTHS SINCE BATCH START DATE</div>
                                @endif
                        <div class="col-md-4">
                            <h4>Counts</h4>

                            @if(isset($stats) && !empty($stats))
                                @forelse($stats as $key => $value)
                                    <b>{{snakeToSentenceCase(snake_case($key))}}</b>: {{$value}}<br>
                                @empty
                                    <p>No stats found</p>
                                @endforelse
                            @endif
                        </div>

                        <div class="col-md-8">
                            <h4>Validation Stats</h4>
                            Total records: {{$validationStats['total']}}<br>

                            Total records with invalid data structure: {{$validationStats['invalid_structure']}}<br>

                            Total records with invalid data: {{$validationStats['invalid_data']}}<br>
                            Missing/invalid mrn: {{$validationStats['mrn']}}<br>
                            Missing/invalid first name: {{$validationStats['first_name']}}<br>
                            Missing/invalid last name: {{$validationStats['last_name']}}<br>
                            Invalid DOB: {{$validationStats['dob']}}<br>
                            0 problems: {{$validationStats['problems']}}<br>
                            0 phones: {{$validationStats['phones']}}<br>

                            @if(!empty($batch->options['errors']))
                                <br><br>

                                <h4>Records not processed due to invalid data</h4>

                                @foreach($batch->options['errors'] as $error)
                                    @if (is_array($error) && array_keys_exist(['row_number', 'message'], $error))
                                        <p>Row {{$error['row_number']}}: {{$error['message']}}</p>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <h4>Processing Options</h4>

                                    @forelse($batch->options as $k => $option)
                                        {{--file paths are too long and mess up the view--}}
                                        @if($k == "filePath") @continue
                                        @endif
                                        @if(!is_array($option))
                                            <b>{{snakeToSentenceCase(snake_case($k))}}</b>
                                            : @if(is_bool($option)) {{!!$option ? 'Yes' : 'No'}} @else {{$option}} @endif<br>
                                        @endif
                                    @empty
                                        <p>No options found.</p>
                                    @endforelse
                                </div>
                            </div>



                            <div class="row" style="margin-top: 10px;">
                                @if($athenaInsurancesExist)
                                    <div class="pull-left" style="padding-left: 2%;">
                                        <a href="{{route('eligibility.download.supplemental_insurance_info', [$batch->id])}}"
                                           class="btn btn-success">Download Supplemental Insurance Info CSV</a>
                                    </div>
                                    <div class="pull-left" style="padding-left: 2%;">
                                        <a href="{{route('eligibility.download.copays', [$batch->id])}}"
                                           class="btn btn-danger">Download Copays CSV</a>
                                    </div>
                                @endif
                            </div>

                        <script>
                            function notifyReportWriter() {
                                var x = document.getElementById("notify");
                                if (x.style.display === "none") {
                                    x.style.display = "block";
                                } else {
                                    x.style.display = "none";
                                }
                            }
                        </script>

                    </div>
                </div>
                @if(optional($initiatorUser)->hasRole('ehr-report-writer'))
                    <div id="notify" class="panel panel-default col-md-6" style="display: none">
                        <div class="container">
                            <h4>Notify EHR Report Writer ({{$initiatorUser->getFullName()}})</h4>
                            <form class="form" action="{{route('report-writer.notify')}}" method="POST">
                                {{csrf_field()}}
                                <div class="form-group">
                                    <br>
                                    {{--<input type="radio" name="status" value="valid" required> Data is valid<br>--}}
                                    {{--<input type="radio" name="status" value="invalid"> Data is invalid<br>--}}
                                    <input type="hidden" name="initiator_id" value="{{$initiatorUser->id}}">
                                    <input type="hidden" name="practice_name"
                                           value="{{$batch->practice->display_name}}">

                                </div>
                                <div class="form-group">
                                <textarea rows="8" cols="70" maxlength="500" class="form-group" name="text"
                                          style="resize: none" required>Hi {{$initiatorUser->first_name}},

This is to let you know that Circle Link Health was able to successfully process the patient data report you uploaded.

Thanks for your hard work.

                            </textarea>
                                </div>
                                <div class="form-group">
                                    <input type="submit" class="btn btn-primary" value="Send">
                                </div>

                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection