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
                        <b>{{ $practice->display_name }}</b> | Batch #{{$batch->id}}
                        | Started: <em>{{ $batch->created_at->format('m-d-Y h:mA') }}</em> | Last Update:
                        <em>{{ $batch->updated_at->format('m-d-Y h:mA')}}</em>
                        <div class="pull-right" style="color: {{$batch->getStatusFontColor()}};">
                            <b>{{ strtoupper($batch->getStatus()) }}</b></div>
                    </div>

                    <div class="panel-body">
                        @if($batch->hasJobs())
                            <div class="pull-left" style="padding-left: 2%;">
                                <a href="{{route('eligibility.download.logs.csv', [$batch->id])}}"
                                   class="btn btn-warning">Download Batch Processing Logs</a>
                            </div>
                        @endif

                        @if($eligible > 0)
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

                        <div class="pull-left" style="padding-left: 2%;">
                            <a href="{{route('get.eligibility.reprocess', [$batch->id])}}"
                               class="btn btn-danger">Reprocess</a>
                        </div>


                        <br><br>

                        <h4>Counts</h4>
                        Eligible: <span id="eligible">{{ $eligible }}</span>

                        @if ($batch->type == App\EligibilityBatch::TYPE_PHX_DB_TABLES)
                            <br>
                            Ineligible & Duplicates: <span
                                    id="ineligible">{{ (int) (App\Models\PatientData\PhoenixHeart\PhoenixHeartName::whereProcessed(true)->count() - $eligible)}}</span>
                            <br>
                            Not processed: <span
                                    id="unprocessed">{{ App\Models\PatientData\PhoenixHeart\PhoenixHeartName::whereProcessed(false)->count() }}</span>
                        @else
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
                            <b>{{snakeToSentenceCase(snake_case($k))}}</b>
                            : @if(is_bool($option)) {{!!$option ? 'Yes' : 'No'}} @else {{$option}} @endif<br>
                        @empty
                            <p>No options found.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection