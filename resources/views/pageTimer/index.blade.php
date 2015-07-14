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
                <div class="panel panel-default">
                    <div class="panel-heading">Add/Edit Page Time</div>
                    <div class="panel-body">


                        <form id="location-form" class="form-horizontal" role="form" method="POST" action="{{ action('PageTimerController@store') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">All Page Times</div>

                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <td><strong>id</strong></td>
                            <td><strong>type</strong></td>
                            <td><strong>duration</strong></td>
                            <td><strong>patient_id</strong></td>
                            <td><strong>provider_id</strong></td>
                            <td><strong>start_time</strong></td>
                            <td><strong>end_time</strong></td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach( $pageTimes as $pageTime )
                            <tr>
                                <td>{{ $pageTime->id }}</td>
                                <td>{{ $pageTime->type }}</td>
                                <td>{{ $pageTime->duration }}</td>
                                <td>{{ $pageTime->patient_id }}</td>
                                <td>{{ $pageTime->provider_id }}</td>
                                <td>{{ $pageTime->start_time }}</td>
                                <td>{{ $pageTime->end_time }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
