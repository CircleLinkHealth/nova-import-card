@extends('partials.adminUI')

<?php

    $active_nurses  = activeNurseNames();

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
            background: url('{{ asset('/vendor/datatables-images/details_open.png') }}') no-repeat center center;
            cursor: pointer;
        }
        tr.shown td.details-control {
            background: url('{{ asset('/vendor/datatables-images/details_close.png') }}') no-repeat center center;
        }
        div.modal-dialog {
            z-index: 1051; /* should ensure the modal body is always visible */
        }
    </style>
@endpush

@section('content')

@endsection