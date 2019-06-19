@extends('partials.adminUI')
@section('content')
    @push('styles')
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>

        <div class="form-group">
            <button onclick="showTotalsTable()" class="btn btn-primary">Show Metrics / Totals</button>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12" id="metrics">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">

                            <div class="panel-heading">Nurse Performance Report</div>

                            <div class="calendar-date" style="padding-left: 2%;">
                                @include('admin.reports.nursesPerformanceForm')
                            </div>

                            <div class="dates">
                                {{$startDate->format('l F jS')}} - {{$endDate->format('l F jS Y')}}
                            </div>

                            <div class="panel-body">
                                <table class="table table-hover" id="nurse_metrics" style="width: 100%">
                                    <thead>
                                    @include('admin.reports.nursePerformanceReportHeadings')
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<br>

        <div class="row">
            <div class="col-md-12 col-lg-12" id="totals" style="display: none">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">

                            <div class="panel-heading">Nurse Performance Totals</div>

                            <div class="dates">
                                {{$startDate->format('l F jS')}} - {{$endDate->format('l F jS Y')}}
                            </div>

                            <div class="panel-body">
                                <table id="" class="totals" style="width:100%">
                                    <thead>
                                    @include('admin.reports.nursePerformanceTotals')
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>

            $(function () {
                var groupColumn = 1;
                $('#nurse_metrics').DataTable({
                    "columnDefs": [
                        {
                            "targets": groupColumn,
                            className: 'left_columns_border'
                        }
                    ],
                    deferRender: true,
                    scrollY: "95%",
                    scrollX: "100%",
                    scrollCollapse: false,
                    processing: true,
                    serverSide: false,
                    paging: false,
                    fixedColumns: {
                        leftColumns: 2,
                    },
                    orderFixed: [1, 'asc'],

                    ajax: {
                        url: '{!! route('admin.reports.nurse.performance.data') !!}',
                        type: "GET",
                        data: function (d) {
                            d.start_date = '{{$startDate}}';
                            d.end_date = '{{$endDate}}';
                        },
                        error: function () {
                            //not always shows accurate response.
                            alert("File does not exist for: '{{$startDate->format('l F jS')}}'")
                        },

                    },
                    columns:
                        [
                            {data: 'name', name: 'name'},
                            {data: 'weekDay', name: 'weekDay'},
                            {data: 'scheduledCalls', name: 'scheduledCalls'},
                            {data: 'actualCalls', name: 'actualCalls'},
                            {data: 'successful', name: 'successful'},
                            {data: 'unsuccessful', name: 'unsuccessful'},
                            {data: 'actualHours', name: 'actualHours'},
                            {data: 'committedHours', name: 'committedHours'},
                            {data: 'completionRate', name: 'completionRate'},
                            {data: 'efficiencyIndex', name: 'efficiencyIndex'},
                            {data: 'caseLoadNeededToComplete', name: 'caseLoadNeededToComplete'},
                            {data: 'hoursCommittedRestOfMonth', name: 'hoursCommittedRestOfMonth'},
                            {data: 'surplusShortfallHours', name: 'surplusShortfallHours'},
                            {data: 'caseLoadComplete', name: 'caseLoadComplete'}
                        ],


                });
//Totals table
                $(document).ready(function () {
                    $('table.totals').DataTable({
                        deferRender: true,
                        scrollY: "95%",
                        scrollX: "100%",
                        scrollCollapse: false,
                        processing: true,
                        // serverSide: true,
                        paging: false,
//@todo: pass data
                    });
                });
            });

        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
        <script src="//cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>
        <script>
            function showTotalsTable() {
                var metrics = document.getElementById("metrics");
                var totals = document.getElementById("totals");
                totals.style.display = "block";
                if (metrics.style.display === "none") {
                    metrics.style.display = "block";
                } else {
                    metrics.style.display = "none";
                }
            }</script>
    @endpush
@stop

<style>
    tr.group,
    tr.group:hover {
        background-color: #71cc85 !important;
    }

    div.dataTables_wrapper {
        width: 100%;
        margin: 0 auto;
    }

    .panel-default > .panel-heading {
        text-align: center;
        font-weight: bold;
        font-size: large;
    }

    .dates {
        font-size: large;
        text-align: center;
        font-weight: bold;
    }

    th, td {
        white-space: nowrap;
    }

    div.dataTables_wrapper {
        width: 100%;
        margin: 0 auto;
    }

    .DTFC_LeftHeadWrapper {
        background-color: #ffffff;
    }

    .left_columns_border {
        border-right: solid 1px #000000;
    }
</style>