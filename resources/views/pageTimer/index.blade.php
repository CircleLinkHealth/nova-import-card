@extends('partials.adminUI')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                @include('core::partials.errors.errors')
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Page Times</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="" class="btn btn-success" disabled="disabled">New Page Time</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Page Times</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>id</strong></td>
                            <td><strong>Title</strong></td>
                            <td><strong>Activity Type</strong></td>
                            <td><strong>Duration</strong></td>
                            <td><strong>Participant</strong></td>
                            <td><strong>Provider</strong></td>
                            <td><strong>processed</strong></td>
                            <td><strong>start/end</strong></td>
                            <td><strong>Rule</strong></td>
                            <td><strong>Activities</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $pageTimes as $pageTime )
                            <tr>
                                <td><a href="{{ route('admin.pagetimer.show', array('id' => $pageTime->id)) }}" class="btn btn-primary">Detail</a></td>
                                <td>{{ $pageTime->title }}</td>
                                <td>{{ $pageTime->activity_type }}</td>
                                <td>{{ $pageTime->duration }} ({{ $pageTime->duration_unit }})</td>
                                <td><a href="{{ route('admin.users.edit', array('id' => $pageTime->patient_id)) }}" class="btn btn-orange btn-xs">{{ $pageTime->patient_id }}</a></td>
                                <td><a href="{{ route('admin.users.edit', array('id' => $pageTime->provider_id)) }}" class="btn btn-orange btn-xs">{{ $pageTime->provider_id }}</a></td>
                                <td>{{ $pageTime->processed }}</td>
                                <td>{{ $pageTime->start_time }}<br>{{ $pageTime->end_time }}</td>
                                <td>
                                    @if (($pageTime->rule_id))
                                        {{ $pageTime->rule_id }} <a href="{{ route('admin.rules.show', array('id' => $pageTime->rule_id)) }}">Rule Detail</a>
                                    @endif
                                </td>
                                <td>
                                    @if (($pageTime->activities->count()))
                                        @foreach( $pageTime->activities as $activity )
                                            <li>{{ $activity->id }} <a href="{{ route('admin.activities.show', array('id' => $activity->id)) }}">Activity Detail</a></li>
                                        @endforeach
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $pageTimes->appends(['action' => 'filter'])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
