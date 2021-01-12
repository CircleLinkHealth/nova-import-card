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
                        <h1>Activities</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="" class="btn btn-success" disabled="disabled">New Activity</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">All Activities</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>id</strong></td>
                            <td><strong>type</strong></td>
                            <td><strong>duration</strong></td>
                            <td><strong>participant</strong></td>
                            <td><strong>provider</strong></td>
                            <td><strong>logger_id</strong></td>
                            <td><strong>date</strong></td>
                            <td><strong>page timer</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $activities as $act )
                            <tr>
                                <td><a href="{{ route('admin.activities.show', array('id' => $act->id)) }}" class="btn btn-primary">Detail</a></td>
                                <td>{{ $act->type }}</td>
                                <td>{{ $act->duration }}</td>
                                <td><a href="{{ route('admin.users.edit', array('id' => $act->patient_id)) }}" class="btn btn-orange btn-xs">{{ $act->patient_id }}</a></td>
                                <td><a href="{{ route('admin.users.edit', array('id' => $act->provider_id)) }}" class="btn btn-orange btn-xs">{{ $act->provider_id }}</a></td>
                                <td><a href="{{ route('admin.users.edit', array('id' => $act->logger_id)) }}" class="btn btn-orange btn-xs">{{ $act->logger_id }}</a></td>
                                <td>{{ $act->performed_at }}</td>
                                <td><a href="{{ route('admin.pagetimer.show', array('id' => $act->page_timer_id)) }}">{{ $act->page_timer_id }}</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $activities->appends(['action' => 'filter'])->render() !!}
                </div>
            </div>
        </div>
    </div>
@stop
