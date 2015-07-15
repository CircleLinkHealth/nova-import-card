@extends('app')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @if (isset($error))
                    <div class="alert alert-danger">
                        <ul>
                            <li>{{ $error }}</li>
                        </ul>
                    </div>
                @endif

                @if (isset($success))
                    <div class="alert alert-success">
                        <ul>
                            <li>{{ $success }}</li>
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">PageTime ID: {{ $pageTime->id }}</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>id</strong></td>
                            <td><strong>duration</strong></td>
                            <td><strong>duration_unit</strong></td>
                            <td><strong>patient_id</strong></td>
                            <td><strong>provider_id</strong></td>
                            <td><strong>program_id</strong></td>
                            <td><strong>start_time</strong></td>
                            <td><strong>end_time</strong></td>
                            <td><strong>processed</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{{ $pageTime->id }} <a href="{{ url('pagetimer/'.$pageTime->id.'') }}">DETAILS</a></td>
                                <td>{{ $pageTime->title }}</td>
                                <td>{{ $pageTime->duration_unit }}</td>
                                <td>{{ $pageTime->patient_id }}</td>
                                <td>{{ $pageTime->provider_id }}</td>
                                <td>{{ $pageTime->program_id }}</td>
                                <td>{{ $pageTime->start_time }}</td>
                                <td>{{ $pageTime->end_time }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <strong>URLS:</strong>
                    <p>IP: {{ $pageTime->ip_addr }}</p>
                    <p>full: {{ $pageTime->url_full }}</p>
                    <p>short: {{ $pageTime->url_short }}</p>

                    <strong>RULES:</strong>
                    <p>activity_type: {{ $pageTime->activity_type }}</p>
                    <p>title: {{ $pageTime->title }}</p>
                    <p>query_string: {{ $pageTime->query_string }}</p>
                    <p>program_id: {{ $pageTime->program_id }}</p>
                    <p>processed: {{ $pageTime->processed }}</p>
                    <p>rule_params: {{ $pageTime->rule_params }}</p>
                    <p>rule_found: {{ $pageTime->rule_found }}</p>
                </div>
            </div>
        </div>
    </div>
@stop
