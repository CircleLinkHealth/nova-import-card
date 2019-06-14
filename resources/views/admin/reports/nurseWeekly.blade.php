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
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">

                            <div class="panel-heading">Nurse Performance Report</div>

                            <div class="calendar-date" style="padding-left: 2%;">
                                @include('admin.reports.nursesWeeklyReportForm')
                            </div>

                            <div class="dates">
                                {{$startOfWeek->format('l F jS')}} - {{max($days)->format('l F jS Y')}}
                            </div>

                            <div class="panel-body">
                                <table class="table table-hover" id="nurse_weekly">
                                    <thead>
                                    @include('admin.reports.nurseWeeklyReportHeadings')
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
                //choose which column to use for grouping (col.13 = weekDays)
                //thinking to make this dynamic
                var groupColumn = 13;
                $('#nurse_weekly').DataTable({
                    "columnDefs": [
                        {
                            "visible": false,
                            "targets": groupColumn,
                        }
                    ],
                    "order": [[groupColumn, 'asc']],
                    "displayLength": 100,
                    "drawCallback": function (settings) {
                        var api = this.api();
                        var rows = api.rows({page: 'current'}).nodes();
                        var last = null;

                        api.column(groupColumn, {page: 'current'}).data().each(function (group, i) {
                            if (last !== group) {
                                $(rows).eq(i).before(
                                    '<tr class="group"><td colspan="13">' + group + '</td></tr>'
                                );

                                last = group;
                            }
                        });

                        // Order by the grouping column
                        $('#nurse_weekly tbody').on('click', 'tr.group', function () {
                                var currentOrder = table.order()[0];
                                if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
                                    table.order([groupColumn, 'desc']).draw();
                                } else {
                                    table.order([groupColumn, 'asc']).draw();
                                }
                            },
                        );
                    },
                    deferRender: true,
                    scrollY: "100%",
                    scrollX: "100%",
                    scrollCollapse: false,
                    processing: true,
                    serverSide: false,
                    fixedColumns: true,


                    ajax: {
                        "url": '{!! route('admin.reports.nurse.weekly.data') !!}',
                        "type": "GET",
                        "data": function (d) {
                            d.date = '{{$date}}'
                        }
                    },
                    columns: [
                        {data: 'name', name: 'name'},
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
                        {data: 'caseLoadComplete', name: 'caseLoadComplete'},
                        //weekDays column is hidden from table view and used only for grouping data by day of week
                        {data: 'weekDay', name: 'weekDay'},
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

    th, td {
        white-space: nowrap;
    }

    div.dataTables_wrapper {
        width: 100%;
        margin: 0 auto;
    }

    .panel-default>.panel-heading{
        text-align: center;
        font-weight: bold;
        font-size: large;
    }

    .dates{
        font-size: large;
        text-align: center;
        font-weight: bold;
    }
</style>