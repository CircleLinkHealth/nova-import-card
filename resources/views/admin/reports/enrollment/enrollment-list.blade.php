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
                            <div class="panel-heading">Enrollment Report</div>

                            <div class="panel-body">
                                <table class="table table-striped" id="enrollment_list">
                                    <thead>
                                    <tr>
                                        <th>
                                            Patient Name
                                        </th>
                                        <th>
                                            Program
                                        </th>
                                        <th>
                                            Provider
                                        </th>
                                        <th>
                                            Has co-pay
                                        </th>
                                        <th>
                                            Care Ambassador
                                        </th>
                                        <th>
                                            Status
                                        </th>
                                        <th>
                                            Total Time Spent
                                        </th>
                                        <th>
                                            Last Call Outcome
                                        </th>
                                        <th>
                                            Last Call Comment
                                        </th>
                                        <th>
                                            MRN Number
                                        </th>
                                        <th>
                                            DOB
                                        </th>
                                        <th>
                                            Phone Number
                                        </th>
                                        <th>
                                            Invite Sent At
                                        </th>
                                        <th>
                                            Invite Opened At
                                        </th>
                                        <th>
                                            Last Attempt At
                                        </th>
                                        <th>
                                            Consented At
                                        </th>
                                        <th>
                                            Preferred Days
                                        </th>
                                        <th>
                                            Preferred Window
                                        </th>
                                        <th>
                                            Other Contact's Name
                                        </th>
                                        <th>
                                            Other Contact's Phone
                                        </th>
                                        <th>
                                            Other Contact's Email
                                        </th>
                                        <th>
                                            Other Contact's Relationship to Patient
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
    </div>
        @push('scripts')
            <script>

                $(function() {
                    $('#enrollment_list').DataTable({
                        processing: true,
                        serverSide: false,
                        "scrollX": true,
                        ajax: '{!! url('/admin/enrollment/list/data') !!}',
                        columns: [
                            {data: 'name', name: 'name'},
                            {data: 'program', name: 'program'},
                            {data: 'provider', name: 'provider'},
                            {data: 'has_copay', name: 'has_copay'},
                            {data: 'care_ambassador', name: 'care_ambassador'},
                            {data: 'status', name: 'status'},
                            {data: 'total_time_spent', name: 'total_time_spent'},
                            {data: 'last_call_outcome', name: 'last_call_outcome'},
                            {data: 'last_call_outcome_reason', name: 'last_call_outcome_reason'},
                            {data: 'mrn_number', name: 'mrn_number'},
                            {data: 'dob', name: 'dob'},
                            {data: 'phone', name: 'phone'},
                            {data: 'invite_sent_at', name: 'invite_sent_at'},
                            {data: 'invite_opened_at', name: 'invite_opened_at'},
                            {data: 'last_attempt_at', name: 'last_attempt_at'},
                            {data: 'consented_at', name: 'consented_at'},
                            {data: 'preferred_days', name: 'preferred_days'},
                            {data: 'preferred_window', name: 'preferred_window'},
                            {data: 'agent_name', name: 'agent_name'},
                            {data: 'agent_phone', name: 'agent_phone'},
                            {data: 'agent_email', name: 'agent_email'},
                            {data: 'agent_relationship', name: 'agent_relationship'},



                        ],
    //                    "aaSorting":[3,'desc'],
                        "iDisplayLength": 25,
                    });

                });


            </script>
            <script src="//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
        @endpush


@stop