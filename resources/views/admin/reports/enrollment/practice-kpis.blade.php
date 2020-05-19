@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
        <style>
            table.dataTable tbody td {
                text-align: center;
            }
        </style>
    @endpush

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('errors.errors')
            </div>
        </div>
        <div class="row" style="margin-left: -52px; margin-right: -55px;">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Practice Enrollment KPIs
                            </div>
                            <div class="panel-body">

                                <div class="col-md-12">
                                    <div class="row" style="padding-bottom: 27px;">

                                        <label class="col-md-1 control-label" for="textinput">Start Date</label>
                                        <input class="col-md-2" id="start_date" name="start_date"
                                               value="{{Carbon\Carbon::now()->startOfMonth()->toDateString()}}"
                                               type="date"
                                               placeholder="placeholder">
                                        <label class="col-md-1 control-label" for="textinput">End Date</label>
                                        <input class="col-md-2" id="end_date" name="end_date"
                                               value="{{Carbon\Carbon::now()->toDateString()}}" type="date"
                                               placeholder="placeholder">
                                    </div>
                                </div>

                                <hr>

                                <table class="table table-striped" id="practice_kpis">
                                    <thead>
                                    <tr>
                                        <th>
                                            Practice Name
                                        </th>
                                        <th>
                                            #Unique Patients Called
                                        </th>
                                        <th>
                                            #Consented
                                        </th>
                                        <th>
                                            #Unable to Contact
                                        </th>
                                        <th>
                                            #Soft Declined
                                        </th>
                                        <th>
                                            #Hard Declined
                                        </th>
                                        <th>
                                            #Incomplete +3 Attempts
                                        </th>
                                        <th>
                                            Labor Hours
                                        </th>
                                        <th>
                                            Conversion %
                                        </th>
                                        <th>
                                            Labor Rate
                                        </th>
                                        <th>
                                            Total Cost
                                        </th>
                                        <th>
                                            Patient Acq. Cost
                                        </th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                var buttonCommon = {
                    exportOptions: {
                        format: {
                            body: function ( data, row, column, node ) {
                                //just in case we want to format data
                                return data;
                            }
                        }
                    }
                };

                $(function () {

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

                    $('#practice_kpis').DataTable({
                        processing: true,
                        serverSide: false,
                        "scrollX": true,
                        ajax: {
                            "url": '{!! url('/admin/enrollment/practice/kpis/data') !!}',
                            "type": "GET",
                            "data": function (d) {
                                d.start_date = $('#start_date').val();
                                d.end_date = $('#end_date').val();
                            }
                        },
                        dom: 'Bfrtip',
                        buttons: [
                            $.extend( true, {}, buttonCommon, {
                                extend: 'copyHtml5',
                            } ),
                            $.extend( true, {}, buttonCommon, {
                                extend: 'excelHtml5',
                                title: 'Practice Enrollment KPIs CSV Export'
                            } ),
                            $.extend( true, {}, buttonCommon, {
                                extend: 'pdfHtml5',
                                orientation: 'landscape',
                                pageSize: 'A4',
                                title: 'Practice Enrollment KPIs PDF Export'
                            } )
                        ],
                        columns: [
                            {data: 'name', name: 'name', className: "text-center"},
                            {data: 'unique_patients_called', name: 'unique_patients_called'},
                            {data: 'consented', name: 'consented'},
                            {data: 'utc', name: 'utc'},
                            {data: 'soft_declined', name: 'soft_declined'},
                            {data: 'hard_declined', name: 'hard_declined'},
                            {data: '+3_attempts', name: '+3_attempts'},
                            {data: 'labor_hours', name: 'labor_hours'},
                            {data: 'conversion', name: 'conversion'},
                            {data: 'labor_rate', name: 'labor_rate'},
                            {data: 'total_cost', name: 'total_cost'},
                            {data: 'acq_cost', name: 'acq_cost'}

                        ],
                        "aaSorting": [2, 'desc'],
                        "iDisplayLength": 15,
                    });

                    $('#practice_kpis')
                        .on('error.dt', function (e, settings, techNote, message) {
                            console.log('An error has been reported by DataTables: ', message);
                        })
                        .DataTable();
                });

                $('#start_date').on('change', function () {
                    console.log($('#start_date').val());
                    $('#practice_kpis').DataTable().ajax.reload();
                });

                $('#end_date').on('change', function () {
                    console.log($('#end_date').val());
                    $('#practice_kpis').DataTable().ajax.reload();
                });
            </script>
            <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
            <script src="//cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.js"></script>
            <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.2.0/jszip.js"></script>
            <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.65/pdfmake.js"></script>
            <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.65/vfs_fonts.js"></script>
            <script src="//cdn.datatables.net/buttons/1.6.2/js/buttons.html5.js"></script>
        @endpush
    </div>

@endsection