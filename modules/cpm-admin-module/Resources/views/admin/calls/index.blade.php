@extends('cpm-admin::partials.adminUI')

@section('content')
<div class="container">
    <table class="table">
        <caption>Calls for {{$patient->getFullName()}}</caption>
        <thead>
        <tr>
            <th>Call Id</th>
            <th>Patient Id</th>
            <th>Call Status</th>
            <th>Call Date</th>
            <th>Call Window</th>
        </tr>
        </thead>
        <tbody>

        @foreach ($calls as $call)
            <tr>
                <td>{{$call->id}}</td>
                <td>{{$call->inbound_cpm_id}}</td>
                <td>{{$call->status}}</td>
                <td>{{$call->called_date}}</td>
                <td>{{$call->window_start}} to {{$call->window_end}}</td>
            </tr>
        @endforeach

        </tbody>
    </table>


{{ $calls->links() }}
@stop