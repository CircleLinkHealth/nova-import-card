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
                        Eligible: {{ $batch->stats['eligible'] }}
                        <br>
                        Ineligible: {{ $batch->stats['ineligible'] }}
                        <br>
                        Duplicates: {{ $batch->stats['duplicates'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush