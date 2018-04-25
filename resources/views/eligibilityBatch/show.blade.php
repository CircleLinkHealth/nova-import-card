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
                        Showing Batch {{$batch->id}}
                    </div>

                    <div class="panel-body">
                        @if($eligible > 0)
                            <div class="pull-right" style="padding-left: 2%;">
                                <a href="{{route('admin.enrollees.show.batch', [$batch->id])}}"
                                   class="btn btn-info">Import Patients</a>
                            </div>

                            <div class="pull-right" style="padding-left: 2%;">
                                <a href="{{route('eligibility.download.eligible', [$batch->id])}}"
                                   class="btn btn-default">Download
                                    Eligible Patients CSV</a>
                            </div>
                        @endif
                        <h4>Practice: {{ $practice->display_name }}</h4>
                        <h4>Process Status: {{ $batch->getStatus() }}</h4>
                        <br>

                        The check was initiated at <b>{{ $batch->created_at }}</b> and the last update on it was at
                        <b>{{ $batch->updated_at }}</b>

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

                        <h4>Batch Details</h4>
                        @isset($batch->options['dir'])
                            Drive Folder ID: {{ $batch->options['dir'] }}
                            <br>
                        @endisset
                        Filtering for Last
                        Encounter?: {{ (boolean) $batch->options['filterLastEncounter'] ? 'Yes' : 'No' }}
                        <br>
                        Filtering for Problems?: {{ (boolean) $batch->options['filterProblems'] ? 'Yes' : 'No' }}
                        <br>
                        Filtering for Insurance
                        (Medicare)?: {{ (boolean) $batch->options['filterInsurance'] ? 'Yes' : 'No' }}
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection