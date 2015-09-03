@extends('app')

@section('content')
    <div class="container-fluid">
        {{-- Create a new key --}}
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @include('errors.errors')
                <div class="panel panel-default">
                    <div class="panel-heading">Add/Edit Activity</div>
                    <div class="panel-body">


                        <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ action('ActivityController@store') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">All Activities</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>id</strong></td>
                            <td><strong>type</strong></td>
                            <td><strong>duration</strong></td>
                            <td><strong>patient_id</strong></td>
                            <td><strong>provider_id</strong></td>
                            <td><strong>logger_id</strong></td>
                            <td><strong>page_timer_id</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $activities as $act )
                            <tr>
                                <td>{{ $act->id }} <a href="{{ url('activities/'.$act->id.'') }}">DETAILS</a></td>
                                <td>{{ $act->type }}</td>
                                <td>{{ $act->duration }}</td>
                                <td>{{ $act->patient_id }}</td>
                                <td>{{ $act->provider_id }}</td>
                                <td>{{ $act->logger_id }}</td>
                                <td>{{ $act->performed_at }}</td>
                                <td><a href="{{ url('pagetimer/'.$act->page_timer_id.'') }}">{{ $act->page_timer_id }}</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
