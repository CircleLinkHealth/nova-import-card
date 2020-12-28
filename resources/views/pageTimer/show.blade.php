@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('core::partials.errors.errors')
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
                                <td><strong>title</strong></td>
                                <td><strong>duration</strong></td>
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
                                <td>{{ $pageTime->id }} <a href="{{ route('admin.pagetimer.show', array('id' => $pageTime->id)) }}">DETAILS</a></td>
                                <td>{{ $pageTime->title }}</td>
                                <td>{{ $pageTime->duration }} ({{ $pageTime->duration_unit }})</td>
                                <td>{{ $pageTime->patient_id }}</td>
                                <td>{{ $pageTime->provider_id }}</td>
                                <td>{{ $pageTime->program_id }}</td>
                                <td>{{ $pageTime->start_time }}</td>
                                <td>{{ $pageTime->end_time }}</td>
                                <td>{{ $pageTime->processed }}</td>
                            </tr>
                            </tbody>
                        </table>
                        <h2>URLS:</h2>
                        <p>IP: {{ $pageTime->ip_addr }}</p>
                        <p>full: {{ $pageTime->url_full }}</p>
                        <p>short: {{ $pageTime->url_short }}</p>

                        <h2>RULES:</h2>
                        <p>activity_type: {{ $pageTime->activity_type }}</p>
                        <p>title: {{ $pageTime->title }}</p>
                        <p>query_string: {{ $pageTime->query_string }}</p>
                        <p>program_id: {{ $pageTime->program_id }}</p>
                        <p>processed: {{ $pageTime->processed }}</p>
                        <p>rule_params: {{ $pageTime->rule_params }} <a href="{{ route('admin.rules.matches', unserialize($pageTime->rule_params)) }}">click here to match rules</a></p>
                        <p>rule_id: {{ $pageTime->rule_id }}</p>
                        @if (($pageTime->rule))
                            <p>found rule:<a href="{{ route('admin.rules.show', array('id' => $pageTime->rule->id)) }}">{{ $pageTime->rule->rule_name }} [{{ $pageTime->rule->id }}]</a></p>
                        @else
                            <p><em>No rule for this page time</em></p>
                        @endif
                        <h2>ACTIVITIES:</h2>
                        @if (($pageTime->activities->count()))
                            <p>Activities generated that are tied to this page time</p>
                            @if (($pageTime->activities->count()))
                                <ul>
                                @foreach( $pageTime->activities as $activity )
                                    <li>{{ $activity->id }} <a href="{{ route('admin.activities.show', array('id' => $activity->id)) }}">Activity Detail</a></li>
                                @endforeach
                                </ul>
                            @endif
                        @else
                            <p><em>No activities for this page time</em></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop