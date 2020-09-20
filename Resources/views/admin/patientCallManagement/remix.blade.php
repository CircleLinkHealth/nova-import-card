@extends('cpm-admin::partials.adminUI')

<?php

    $active_nurses = activeNurseNames();

?>

@push('styles')
    <style>
        #calls-table tbody>tr>td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .cpm-editable {
            color:#000;
        }

        .highlight {
            color:green;
            font-weight:bold;
        }
        td.details-control {
            color:#fff;
            background: url('{{ mix('/vendor/datatables-images/details_open.png') }}') no-repeat center center;
            cursor: pointer;
        }
        tr.shown td.details-control {
            background: url('{{ mix('/vendor/datatables-images/details_close.png') }}') no-repeat center center;
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
                    <h1>Patient Call Management</h1>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Manage Patient Calls</div>
                        <div class="panel-body">
                            <div>
                                @include('core::partials.errors.errors')
                                @include('errors.messages')
                            </div>
                            <div>
                                <call-mgmt-app ref="callMgmtApp"></call-mgmt-app>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection