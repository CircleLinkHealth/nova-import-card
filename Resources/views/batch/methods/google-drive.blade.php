@extends('cpm-admin::partials.adminUI')

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

            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Process Eligibility from Google Drive</div>

                    <div class="panel-body">
                        @if(isset($action) && $action == 'edit')
                            @include('eligibility::partials.makeWelcomeCallListGoogleDrivePanel')
                        @else
                            @include('eligibility::partials.makeWelcomeCallListGoogleDrivePanel')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection