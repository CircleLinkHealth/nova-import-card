@extends('partials.adminUI')

@section('content')

    <script>
        $(document).ready(function () {
            $(".practices").select2();

        });
    </script>

    <style>
        .select2-container {
            width: 300px !important;
        }
    </style>

    <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">

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
                            <div class="panel-heading">Billable Patients Report</div>
                            <div class="panel-body">

                                <div class="col-md-12">
                                    <div class="row" style="padding-bottom: 27px;">

                                        <label class="col-md-1 control-label" for="practice_id">Select Practice</label>
                                        <select class="col-md-4 practices dropdown Valid form-control" name="practice_id" id="practice_id">

                                            <option value="0">All</option>
                                            @foreach($practices as $practice)
                                                <option value="{{$practice->id}}">{{$practice->display_name}}</option>
                                            @endforeach

                                        </select>

                                    </div>
                                </div>

                                <hr>
                            <div class="panel-body">
                                <table class="table table-striped" id="billable_list">
                                    <thead>
                                    <tr>
                                        <th>
                                            Provider Name
                                        </th>
                                        <th>
                                            Patient Name
                                        </th>
                                        <th>
                                            Practice Name
                                        </th>
                                        <th>
                                            DOB
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            CCM (Mins)
                                        </th>
                                        <th>
                                            Problem 1
                                        </th>
                                        <th>
                                            Problem 2
                                        </th>
                                        <th>
                                            Approve
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

        <script>

            $(function () {
                $('#billable_list').DataTable({
                    processing: true,
                    serverSide: false,
                    "scrollX": true,
                    ajax: {
                        "url": '{!! url('/admin/reports/monthly-billing/v2/data') !!}',
                        "type": "POST",
                        "data": function (d) {
                            d.practice_id = $('#practice_id').val();
                        }
                    },

                    columns: [
                        {data: 'name', name: 'name'},
                        {data: 'provider', name: 'provider'},
                        {data: 'practice', name: 'practice'},
                        {data: 'dob', name: 'dob'},
                        {data: 'status', name: 'status'},
                        {data: 'ccm', name: 'ccm'},
                        {data: 'problem1', name: 'problem1'},
                        {data: 'problem2', name: 'problem2'},
                        {data: 'approve', name: 'approve'},
                    ],
                    "iDisplayLength": 25,
                    "aaSorting":[1,'desc'],
                    "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        if (aData['background_color'] != '') {
                            $('td', nRow).css('background-color', aData['background_color']);
                        }
                    },

                });

            });

            $('#practice_id').on('change', function () {
                $('#billable_list').DataTable().ajax.reload();
            });

            $('#billable_list')
                .on('error.dt', function (e, settings, techNote, message) {
                    console.log('An error has been reported by DataTables: ', message);
                })
                .DataTable();


        </script>
        <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>


@stop