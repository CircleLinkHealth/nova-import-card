@extends('partials.adminUI')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    @endpush
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-sm-8">
                        <h2>View Observation</h2>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">View Observation: {{ $observation->id }}</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <td><strong>obs_msg_id</strong></td>
                                <td><strong>user_id</strong></td>
                                <td><strong>sequence_id</strong></td>
                                <td><strong>obs_date</strong></td>
                                <td><strong>obs_key</strong></td>
                                <td><strong>obs_method</strong></td>
                                <td><strong>obs_value</strong></td>
                                <td><strong>obs_unit</strong></td>
                                <td><strong>program_id</strong></td>
                                <td></td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><div class="btn btn-orange btn-xs">{{ $observation->obs_message_id }}</div></td>
                                <td>{{ $observation->user_id }}</td>
                                <td>{{ $observation->sequence_id }}</td>
                                <td>{{ $observation->obs_date }}</td>
                                <td>{{ $observation->obs_key }}</td>
                                <td>{{ $observation->obs_method }}</td>
                                <td>{{ $observation->obs_value }}</td>
                                <td>{{ $observation->obs_unit }}</td>
                                <td>{{ $observation->program_id }}</td>
                                <td>
                                    @if(Cerberus::can('observations-edit'))
                                        <a href="{{ URL::route('admin.observations.edit', array('id' => $observation->id)) }}" class="btn btn-primary">Edit</a>
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Legacy Obs Id:</strong><br>
                            {{ $observation->legacy_obs_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Msg ID:</strong><br>
                            {{ $observation->obs_message_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Comment Id:</strong><br>
                            {{ $observation->comment_id }}
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Obs Key:</strong><br>
                            <p>{{ $observation->obs_key }}</p>
                        </div>

                        <div class="row" style="margin:20px 0px;">
                            <strong>Legacy Obs Id:</strong><br>
                            {{ $observation->legacy_obs_id }}
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>
@stop
