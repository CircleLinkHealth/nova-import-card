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
                            <td><strong>patient</strong></td>
                            <td><strong>provider</strong></td>
                            <td><strong>logger_id</strong></td>
                            <td><strong>date</strong></td>
                            <td><strong>page timer</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $activities as $act )
                            <tr>
                                <td><a href="{{ url('activities/'.$act->id.'') }}" class="btn btn-primary">Detail</a></td>
                                <td>{{ $act->type }}</td>
                                <td>{{ $act->duration }}</td>
                                <td><a href="{{ URL::route('usersEdit', array('id' => $act->patient_id)) }}" class="btn btn-orange btn-xs">{{ $act->patient_id }}</a></td>
                                <td><a href="{{ URL::route('usersEdit', array('id' => $act->provider_id)) }}" class="btn btn-orange btn-xs">{{ $act->provider_id }}</a></td>
                                <td><a href="{{ URL::route('usersEdit', array('id' => $act->logger_id)) }}" class="btn btn-orange btn-xs">{{ $act->logger_id }}</a></td>
                                <td>{{ $act->performed_at }}</td>
                                <td><a href="{{ url('pagetimer/'.$act->page_timer_id.'') }}">{{ $act->page_timer_id }}</a></td>
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
