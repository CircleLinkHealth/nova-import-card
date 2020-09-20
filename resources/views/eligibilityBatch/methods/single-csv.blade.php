@extends('partials.adminUI')

@push('styles')
    <style>
        .batch-body {
            padding: 20px;
            border: 5px solid #eee;
            margin-bottom: 30px;
        }
    </style>
@endpush

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
            @include('core::partials.errors.errors')
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Process Eligibility from CSV</div>

                    <div class="panel-body">
                        @include('partials.makeWelcomeCallsListUploadPanel')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection