@extends('layouts.master')

@section('content')
    <h1>CALLS</h1>
    <table class="table table-bordered" id="calls-table">
        <thead>
        <tr>
            <th>Id</th>
            <th>Date</th>
            <th>Window Start</th>
            <th>Window End</th>
            <th>Body</th>
        </tr>
        </thead>
    </table>

    <h1>USERS</h1>
    <table class="table table-bordered" id="users-table">
        <thead>
        <tr>
            <th>Id</th>
            <th>Name</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Updated At</th>
        </tr>
        </thead>
    </table>
@stop

@push('scripts')
<script>
    $(function() {
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('datatables.data') }}',
                columns: [
                    {data: 0, name: 'ID'},
                    {data: 1, name: 'display_name'},
                    {data: 2, name: 'user_email'},
                    {data: 3, name: 'created_at'},
                    {data: 4, name: 'updated_at'}
        ]
        });
    });

    $(function() {
        $('#calls-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('datatables.anyDataCalls') }}',
            columns: [
                {data: 'id', name: 'calls.id'},
                {data: 'call_date', name: 'call_date'},
                {data: 'window_start', name: 'window_start'},
                {data: 'window_end', name: 'window_end'},
                {data: 'body', name: 'body'}
            ]
        });
    });
</script>
@endpush
