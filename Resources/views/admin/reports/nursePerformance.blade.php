@extends('cpm-admin::partials.adminUI')
@section('content')
    @push('styles')
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('core::partials.errors.errors')
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-12" id="metrics">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">

                            <div class="panel-heading">Nurse Performance Report</div>

                            <div class="calendar-date" style="padding-left: 2%; padding-top: 1%;">
                                @include('admin.reports.nursesPerformanceForm')
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
                var columnsForTextFormating = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14];

                function setExcelExportHref(startDate, endDate) {
                    var href = $('.excel-export').attr('data-href') + '?start_date=' + startDate + '&end_date=' + endDate
                    $('.excel-export').attr('href', href)
                    return href
                }

                $("#start_date").change(function (date) {
                    // whatever you need to be done on change of the input field
                    setExcelExportHref($("#start_date").val(), $("#end_date").val())
                });

                $("#end_date").change(function (date) {
                    // whatever you need to be done on change of the input field
                    setExcelExportHref($("#start_date").val(), $("#end_date").val())
                });

                setExcelExportHref($("#start_date").val(), $("#end_date").val())

                $('#nurse_metrics').DataTable({
                    "rowCallback": function (row, data) {
                        if (data.Name === "Z - Totals for:") {
                            const color = '#32C132';
                            data.Name.replace('Z - ', '<span style="color:'+color+';">Z - </span>')
                            $('td:eq(1)', row).css(
                                {
                                    'background-color': color,
                                }
                            );
                        }

                        // Define WORK HOURS text color
                        const workHrsPercentage = ((data['Actual Hrs Worked'] / data['Committed Hrs']) * 100);
                        const hoursWorkedColumn = 'td:eq(2)';

                        // Green if hours worked is >=95% of committed.
                        if (workHrsPercentage >= 95) {
                            $(hoursWorkedColumn, row).css(
                                {
                                    'color': '#32C132',
                                }
                            );
                        }

                        // Yellow if hours worked is 85% - 94% of committed.
                        if (workHrsPercentage >= 85 && workHrsPercentage <= 94) {
                            $(hoursWorkedColumn, row).css(
                                {
                                    'color': '#ffcf10',
                                }
                            );
                        }

                        // Red if hours worked is <84% of committed.
                        if (workHrsPercentage <= 84) {
                            $(hoursWorkedColumn, row).css(
                                {
                                    'color': '#ff8900',
                                }
                            );
                        }
                        // END OF _ Define WORK HOURS text color.
                        // -------------------------------------

                        const hrsDefsColumn = 'td:eq(11)';
                        const hrsDeficit = parseFloat(data['Hrs Deficit or Surplus']);
                        if (hrsDeficit < 0) {
                            $(hrsDefsColumn, row).css('color', '#FA5353');
                        }
                        if (hrsDeficit > 0) {
                            $(hrsDefsColumn, row).css('color', '#32C132');
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
                        },
                    ],

                    deferRender: true,
                    scrollY: "auto",
                    scrollX: "100%",
                    scrollCollapse: false,
                    processing: true,
                    serverSide: true,
                    paging: false,
                    //fixed columns disables ability to sort data on demand.
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
                            alert("File does not exist for: '{{$startDate->format('l F jS')}}'")
                        },

                    },


                    columns:
                        [
                            {data: 'Day', name: 'Day'},
                            {data: 'Name', name: 'Name'},
                            {data: 'Actual Hrs Worked', name: 'Actual Hrs Worked'},
                            {data: 'Committed Hrs', name: 'Committed Hrs'},
                            // {data: 'Assigned Calls', name: 'Assigned Calls'},
                            {data: 'Actual Calls', name: 'Actual Calls'},
                            {data: 'Successful Calls', name: 'Successful Calls'},
                            {data: 'Unsuccessful Calls', name: 'Unsuccessful Calls'},
                            {data: 'Avg CCM Time Per Successful Patient', name: 'Avg CCM Time Per Successful Patient'},
                            {data: 'Avg Completion Time Per Patient', name: 'Avg Completion Time Per Patient'},
                            {data: 'Est. Hrs to Complete Case Load', name: 'Est. Hrs to Complete Case Load'},
                            // {data: 'Attendance/Calls Completion Rate', name: 'Attendance/Calls Completion Rate'},
                            // {data: 'Efficiency Index', name: 'Efficiency Index'},
                            // {data: 'Projected Hrs. Left In Month', name: 'Projected Hrs. Left In Month'},
                            {data: 'Hrs Committed Rest of Month', name: 'Hrs Committed Rest of Month'},
                            {data: 'Hrs Deficit or Surplus', name: 'Hrs Deficit or Surplus'},
                            {data: 'Case Load', name: 'Case Load'},
                            {data: 'Incomplete Patients', name: 'Incomplete Patients'},
                            {data: '% Case Load Complete', name: '% Case Load Complete'},
                        ],
                });

            });

        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
        <script src="//cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>


    @endpush
@stop

<style>
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
        font-size: medium;
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

    .table.dataTable.no-footer {
        border-bottom: unset;
    }
</style>