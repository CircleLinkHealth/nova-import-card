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
        <div class="row" style="margin-left: -52px; margin-right: -55px;">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Practice Enrollment KPIs</div>
                            <div class="panel-body">

                                <div class="col-md-12">
                                    <div class="row" style="padding-bottom: 27px;">

                                        <label class="col-md-1 control-label" for="textinput">Start Date</label>
                                        <input class="col-md-2" id="start_date" name="start_date"
                                               value="{{Carbon\Carbon::now()->subWeek()->toDateString()}}" type="date"
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
                                            #Rejected
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

                $(function () {

                    $("#start_date").change(function () {
                        // whatever you need to be done on change of the input field
                    });

                    $("#end_date").change(function () {
                        // whatever you need to be done on change of the input field
                    });

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
                        columns: [

                            {data: 'name', name: 'name'},
                            {data: 'unique_patients_called', name: 'unique_patients_called'},
                            {data: 'consented', name: 'consented'},
                            {data: 'utc', name: 'utc'},
                            {data: 'rejected', name: 'rejected'},
                            {data: 'labor_hours', name: 'labor_hours'},
                            {data: 'conversion', name: 'conversion'},
                            {data: 'labor_rate', name: 'labor_rate'},
                            {data: 'total_cost', name: 'total_cost'},
                            {data: 'acq_cost', name: 'acq_cost'}

                        ],
                        "aaSorting": [2, 'desc'],
                        "iDisplayLength": 15,
                    });

                });

                $('#start_date').on('change', function () {
                    console.log($('#start_date').val());
                    $('#practice_kpis').DataTable().ajax.reload();
                });

                $('#end_date').on('change', function () {
                    console.log($('#end_date').val());
                    $('#practice_kpis').DataTable().ajax.reload();
                });

                $.fn.dataTable.ext.errMode = 'none';

                $('#practice_kpis')
                    .on('error.dt', function (e, settings, techNote, message) {
                        console.log('An error has been reported by DataTables: ', message);
                    })
                    .DataTable();

            </script>
            <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
        @endpush


@stop