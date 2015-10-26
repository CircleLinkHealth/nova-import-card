@extends('app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="row">
                    <div class="col-sm-8">
                        <h1>Locations</h1>
                    </div>
                    <div class="col-sm-4">
                        <div class="pull-right" style="margin:20px;">
                            <a href="{{ action('LocationController@create') }}" class="btn btn-success">New Location</a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Locations</div>
                    <div class="panel-body">
                        @include('errors.errors')
                        <strong>Parent Locations:</strong><BR>
                        @foreach( $locationParents as $id => $loc )
                            <a href='/locations/{{ $id }}'>{{ $id }} -- {{ $loc }}</a> <BR>
                        @endforeach

                        <strong>Parents Sub Locations:</strong><BR>
                        @foreach( $locationParentsSubs as $id => $loc )
                            {{ $id }} -- {{ $loc }} <BR>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
