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

                function setExcelExportHref (startDate, endDate) {
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
                            $('td:eq(0)', row).css(
                                {
                                    'background-color': '#32C132',
                                }
                            );
                        }

                        if ( data['Hrs Deficit or Surplus'] < 0) {
                            $('td:eq(13)', row).css('color', '#FA5353');
                        } else {
                            $('td:eq(13)', row).css('color', '#32C132');
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
                            {data: 'Name', name: 'Name'},
                            {data: 'Day', name: 'Day'},
                            {data: 'Assigned Calls', name: 'Assigned Calls'},
                            {data: 'Actual Calls', name: 'Actual Calls'},
                            {data: 'Successful Calls', name: 'Successful Calls'},
                            {data: 'Unsuccessful Calls', name: 'Unsuccessful Calls'},
                            {data: 'Actual Hrs Worked', name: 'Actual Hrs Worked'},
                            {data: 'Committed Hrs', name: 'Committed Hrs'},
                            {data: 'Attendance/Calls Completion Rate', name: 'Attendance/Calls Completion Rate'},
                            {data: 'Efficiency Index', name: 'Efficiency Index'},
                            {data: 'Est. Hrs to Complete Case Load', name: 'Est. Hrs to Complete Case Load'},
                            {data: 'Projected Hrs. Left In Month', name: 'Projected Hrs. Left In Month'},
                            {data: 'Hrs Committed Rest of Month', name: 'Hrs Committed Rest of Month'},
                            {data: 'Hrs Deficit or Surplus', name: 'Hrs Deficit or Surplus'},
                            {data: 'Case Load', name: 'Case Load'},
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