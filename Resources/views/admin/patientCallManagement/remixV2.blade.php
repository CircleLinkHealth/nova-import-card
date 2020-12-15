<?php
$isAdmin = ! auth()->guest() && auth()->user()->hasPermission('admin-access');
?>

@extends($isAdmin ? 'cpm-admin::partials.adminUI' : 'cpm-admin::partials.non-adminUI')

@section('title', 'Patient Activity Management')
@section('activity', 'Patient Activity Management')

@push('styles')
    <style>
        #calls-table tbody > tr > td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .cpm-editable {
            color: #000;
        }

        .highlight {
            color: green;
            font-weight: bold;
        }

        td.details-control {
            color: #fff;
            background: url('{{ asset('/vendor/datatables-images/details_open.png') }}') no-repeat center center;
            cursor: pointer;
        }

        tr.shown td.details-control {
            background: url('{{ asset('/vendor/datatables-images/details_close.png') }}') no-repeat center center;
        }

        div.modal-dialog {
            z-index: 1051; /* should ensure the modal body is always visible */
        }

        div.main-section {
            width: 94%;
            margin-left: 3%;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-11 main-section">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Patient Activity Management</h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div>
                                    @include('core::partials.errors.errors')
                                    @include('core::partials.errors.messages')
                                </div>
                                <div>
                                    <call-mgmt-app-v2 ref="callMgmtAppV2" provider-app-url="{{env('CPM_PROVIDER_APP_URL') ?? env('APP_URL')}}" auth-role="{{auth()->user()->practiceOrGlobalRole()->name}}"></call-mgmt-app-v2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection