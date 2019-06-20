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
    </div>


    @push('scripts')
        <script>
            $(function () {
                var columnForBorder = 1;
                var columnsForTextFormating = [1,2,3,4,5,6,7,8,9,10,11,12,13,14];

                $('#nurse_metrics').DataTable({
                    "rowCallback": function (row, data) {
                        if (data.name === "Z - Totals for:") {
                            $('td:eq(0)', row).css('background-color', '#21F6C4');
                        }
                    },
                    "columnDefs": [
                        {
                            className: 'left_columns_border',
                            targets: columnForBorder,
                        },

                        {
                            className: 'dt-center',
                            targets: columnsForTextFormating
                        }
                    ],
                    deferRender: true,
                    scrollY: "95%",
                    scrollX: "100%",
                    scrollCollapse: false,
                    processing: true,
                    serverSide: true,
                    paging: false,
                    //fixed columns disables ability to sort data on demand. Can we fix this?
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
                            {data: 'projectedHoursLeftInMonth', name: 'projectedHoursLeftInMonth'},
                            {data: 'hoursCommittedRestOfMonth', name: 'hoursCommittedRestOfMonth'},
                            {data: 'surplusShortfallHours', name: 'surplusShortfallHours'},
                            {data: 'caseLoadComplete', name: 'caseLoadComplete'},
                        ],
                });

            });

        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
        <script src="//cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>

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