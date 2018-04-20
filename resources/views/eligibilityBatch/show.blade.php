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
                    <div class="panel-heading">Showing Batch {{$batch->id}}</div>

                    <div class="panel-body">
                        <h4>Practice Slug: {{ $batch->options['practiceName'] }}</h4>
                        <h4>Process Status: {{ $batch->getStatus() }}</h4>
                        <br>

                        <h4>Counts</h4>
                        Eligible: {{ $batch->stats['eligible'] }}
                        <br>
                        Ineligible: {{ $batch->stats['ineligible'] }}
                        <br>
                        Duplicates: {{ $batch->stats['duplicates'] }}

                        <br><br>

                        <h4>Batch Details</h4>
                        Drive Folder ID: {{ $batch->options['dir'] }}
                        <br>
                        Filtering for Last Encounter?: {{ (boolean) $batch->options['filterLastEncounter'] ? 'Yes' : 'No' }}
                        <br>
                        Filtering for Problems?: {{ (boolean) $batch->options['filterProblems'] ? 'Yes' : 'No' }}
                        <br>
                        Filtering for Insurance (Medicare)?: {{ (boolean) $batch->options['filterInsurance'] ? 'Yes' : 'No' }}
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush