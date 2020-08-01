@extends('partials.adminUI')

@push('styles')
    <style>
        .batch-body {
            min-height: 30rem;
            padding: 20px;
            border: 5px solid #eee;
            margin-bottom: 30px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="pull-left">
                                    <h4>Eligibility Processing Panel</h4>
                                </div>

                                <div class="pull-right">
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenu2"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Process New Records
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                                            <li><a href="{{route('eligibility.batches.google.drive.create')}}">From
                                                    Google Drive</a></li>
                                            <li role="separator" class="divider"></li>
                                            <li><a href="{{route('report-writer.dashboard')}}">CSV, other format</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div class="row">
                            @foreach($batches as $batch)
                                <div class="col-md-3">
                                    <div class="batch-body alert alert-{{$batch->cssClass}}">
                                        <div class="row">
                                            <h5 class="col-md-9">
                                                <div class="pull-left">
                                                    <h4>{{ $batch->practice->display_name }}</h4>
                                                </div>
                                            </h5>
                                            <h5 class="col-md-3">
                                                <div class="pull-right">
                                                    <span class="alert-{{$batch->cssClass}}">{{ $batch->statusPretty }}</span>
                                                </div>
                                            </h5>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <p>Type <b>{{ $batch->getType() }}</b></p>
                                                <p>Started <b>{{ $batch->created_at }}</b></p>
                                                <p>Last update <b>{{ $batch->updated_at }}</b>.</p>
                                            </div>
                                        </div>

                                        <br>
                                        <br>
                                        <div class="pull-right">
                                            {{link_to_route('eligibility.batch.show', 'View Batch', [$batch->id], ['class' => 'btn btn-info'])}}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection